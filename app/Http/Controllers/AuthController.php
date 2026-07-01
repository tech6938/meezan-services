<?php

namespace App\Http\Controllers;

use App\Models\{BookingRequest, Role, ServiceRequest, User};
use App\Models\Setting;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Mail};
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function dashboard(Request $request)
    {
        // Get date range from request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Base queries for stats
        $completeBookingsQuery = BookingRequest::where('status', 'complete_booking');
        $NewBookingsQuery = BookingRequest::where('status', 'pending');
        $customers = User::count() ?? 0;
        $totalRequests = ServiceRequest::count() ?? 0;
        $totalCommission = DB::table('commission_logs')->sum('commission_deducted') ?? 0;
        $setting = Setting::first();
        $appIsOn = $setting ? $setting->appIsOn : 1;

        // Apply date filter to booking counts
        if ($startDate && $endDate) {
            $completeBookingsQuery->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
            $NewBookingsQuery->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        }

        $completeBookings = $completeBookingsQuery->count();
        $NewBookings = $NewBookingsQuery->count();

        // Get most booked categories (where assigned = 1)
        $mostBookedCategories = $this->getMostBookedCategories($startDate, $endDate);

        // Get most booked subcategories (where assigned = 1)
        $mostBookedSubcategories = $this->getMostBookedSubcategories($startDate, $endDate);

        // Get chart data
        $chartData = $this->getChartData($startDate, $endDate);

        return view('dashboard', compact(
            'completeBookings',
            'NewBookings',
            'customers',
            'totalRequests',
            'totalCommission',
            'mostBookedCategories',
            'mostBookedSubcategories',
            'startDate',
            'endDate',
            'appIsOn',
            'chartData'
        ));
    }

    /**
     * Get data for charts
     */
    private function getChartData($startDate = null, $endDate = null)
    {
        // 1. Monthly Bookings Chart Data (Last 12 months or filtered range)
        $monthlyBookings = $this->getMonthlyBookingsData($startDate, $endDate);

        // 2. Booking Status Distribution Chart Data
        $statusDistribution = $this->getStatusDistributionData($startDate, $endDate);

        // 3. Top Categories Chart Data
        $topCategories = $this->getTopCategoriesData($startDate, $endDate);

        // 4. Daily Bookings Chart Data (for selected range)
        $dailyBookings = $this->getDailyBookingsData($startDate, $endDate);

        // 5. Revenue Trend Chart Data
        $revenueTrend = $this->getRevenueTrendData($startDate, $endDate);

        return [
            'monthlyBookings' => $monthlyBookings,
            'statusDistribution' => $statusDistribution,
            'topCategories' => $topCategories,
            'dailyBookings' => $dailyBookings,
            'revenueTrend' => $revenueTrend,
        ];
    }


    private function getMostBookedCategories($startDate = null, $endDate = null)
    {
        $query = BookingRequest::join('service_requests', 'booking_requests.request_id', '=', 'service_requests.id')
            ->join('main_categories', 'service_requests.cat_id', '=', 'main_categories.id')
            ->where('booking_requests.assigned', 1)
            ->select(
                'main_categories.id',
                'main_categories.name',
                'main_categories.urdu_name',
                'main_categories.image',
                DB::raw('COUNT(booking_requests.id) as total_bookings')
            )
            ->groupBy('main_categories.id', 'main_categories.name', 'main_categories.urdu_name', 'main_categories.image')
            ->orderBy('total_bookings', 'DESC')
            ->limit(10);

        if ($startDate && $endDate) {
            $query->whereBetween('booking_requests.created_at', [$startDate, $endDate . ' 23:59:59']);
        }

        return $query->get();
    }

    private function getMostBookedSubcategories($startDate = null, $endDate = null)
    {
        $query = BookingRequest::join('service_requests', 'booking_requests.request_id', '=', 'service_requests.id')
            ->join('sub_categories', 'service_requests.subcat_id', '=', 'sub_categories.id')
            ->join('main_categories', 'sub_categories.cat_id', '=', 'main_categories.id')
            ->where('booking_requests.assigned', 1)
            ->select(
                'sub_categories.id',
                'sub_categories.name',
                'sub_categories.urdu_name',
                'sub_categories.image',
                'main_categories.name as category_name',
                DB::raw('COUNT(booking_requests.id) as total_bookings')
            )
            ->groupBy('sub_categories.id', 'sub_categories.name', 'sub_categories.urdu_name', 'sub_categories.image', 'main_categories.name')
            ->orderBy('total_bookings', 'DESC')
            ->limit(10);

        if ($startDate && $endDate) {
            $query->whereBetween('booking_requests.created_at', [$startDate, $endDate . ' 23:59:59']);
        }

        return $query->get();
    }

    /**
     * Get monthly bookings data
     */
    private function getMonthlyBookingsData($startDate = null, $endDate = null)
    {
        $query = BookingRequest::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        } else {
            // Last 12 months if no filter
            $query->where('created_at', '>=', now()->subMonths(12));
        }

        $data = $query->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $months = [];
        $counts = [];

        foreach ($data as $item) {
            $monthName = date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year));
            $months[] = $monthName;
            $counts[] = $item->total;
        }

        return [
            'months' => $months,
            'counts' => $counts,
        ];
    }

    /**
     * Get status distribution data
     */
    private function getStatusDistributionData($startDate = null, $endDate = null)
    {
        $query = BookingRequest::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        }

        $data = $query->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $statuses = [];
        $counts = [];
        $colors = [];

        $colorMap = [
            'pending' => '#FFC107',
            'accept' => '#4CAF50',
            'in_progress' => '#2196F3',
            'complete_booking' => '#8BC34A',
            'cancel' => '#F44336',
        ];

        foreach ($data as $item) {
            $statuses[] = ucfirst(str_replace('_', ' ', $item->status));
            $counts[] = $item->count;
            $colors[] = $colorMap[$item->status] ?? '#9E9E9E';
        }

        return [
            'statuses' => $statuses,
            'counts' => $counts,
            'colors' => $colors,
        ];
    }

    /**
     * Get top categories data
     */
    private function getTopCategoriesData($startDate = null, $endDate = null)
    {
        $query = BookingRequest::join('service_requests', 'booking_requests.request_id', '=', 'service_requests.id')
            ->join('main_categories', 'service_requests.cat_id', '=', 'main_categories.id')
            ->selectRaw('main_categories.name, COUNT(booking_requests.id) as total')
            // ->where('booking_requests.goto', '1')
            ->groupBy('main_categories.id', 'main_categories.name')
            ->orderBy('total', 'desc')
            ->limit(5);

        if ($startDate && $endDate) {
            $query->whereBetween('booking_requests.created_at', [$startDate, $endDate . ' 23:59:59']);
        }

        $data = $query->get();

        $categories = [];
        $counts = [];
        $colors = ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336'];

        foreach ($data as $index => $item) {
            $categories[] = $item->name;
            $counts[] = $item->total;
        }

        return [
            'categories' => $categories,
            'counts' => $counts,
            'colors' => $colors,
        ];
    }

    /**
     * Get daily bookings data
     */
    private function getDailyBookingsData($startDate = null, $endDate = null)
    {
        $query = BookingRequest::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        } else {
            // Last 30 days if no filter
            $query->where('created_at', '>=', now()->subDays(30));
            $startDate = now()->subDays(30)->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        }

        $data = $query->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $dates = [];
        $counts = [];

        // Fill missing dates
        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            (new \DateTime($endDate))->modify('+1 day')
        );

        $dateCounts = $data->pluck('total', 'date')->toArray();

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $dates[] = $dateStr;
            $counts[] = $dateCounts[$dateStr] ?? 0;
        }

        return [
            'dates' => $dates,
            'counts' => $counts,
        ];
    }

    /**
     * Get revenue trend data
     */
    private function getRevenueTrendData($startDate = null, $endDate = null)
    {
        $query = BookingRequest::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        } else {
            $query->where('created_at', '>=', now()->subMonths(6));
        }

        $data = $query->where('status', 'complete_booking')
            ->selectRaw('DATE_FORMAT(created_at, "%b %Y") as month, SUM(price) as total')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $months = [];
        $revenue = [];

        foreach ($data as $item) {
            $months[] = $item->month;
            $revenue[] = $item->total;
        }

        return [
            'months' => $months,
            'revenue' => $revenue,
        ];
    }




    // signup
    public function signup()
    {
        $role = Role::all();
        return view('auth.signup', compact('role'));
    }
    //insert signup
    public function insert_signup(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'role' => 'required',
            'password' => 'required|confirmed|max:8',
        ]);

        $insert = User::create([
            'name' => $request->first_name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);
        if ($insert) {
            auth()->login($insert);
            return redirect()->route('dashboard');
        }
    }
    // login
    public function login()
    {
        return view('auth.login');
    }
    // math login
    public function match_login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|max:8',
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->route('dashboard');
        }

        return redirect()->back()->with('error', 'Admin Not Found');
    }
    // logout
    public function logout()
    {
        Auth::guard('admin')->logout();

        return redirect()->route('login')->with('info', 'Are you sure you want to logout?');
    }
    // forgot
    public function forget()
    {
        return view('auth.forget');
    }
    // forgot password message ====
    public function forget_message(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:users,email',
        ]);

        $message = random_int(1000, 9999);
        $messageText = (string) $message;

        // Store OTP in session
        session(['otp' => $message, 'otp_email' => $request->email]);

        Mail::raw($messageText, function ($mail) use ($request) {
            $mail->to($request->email)
                ->subject('Direct Message');
        });

        return redirect()->route('otp')->with('success', 'Message sent successfully');
    }

    // otp
    function otp()
    {
        return view('auth.otp');
    }
    public function matching_route(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        $sessionOtp = session('otp');
        $email = session('otp_email');

        if ($request->otp == $sessionOtp) {
            // OTP is correct, clear it from session
            session()->forget(['otp']);

            // Redirect to reset password page
            return redirect()->route('reset')->with('success', 'OTP verified successfully');
        } else {
            return redirect()->back()->with('error', 'Invalid OTP');
        }
    }


    // reset
    public function reset()
    {
        return view('auth.reset');
    }
    public function update_password(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|max:8',
        ]);

        $email = session('otp_email'); // email stored during OTP

        if (!$email) {
            return redirect()->route('forget')->with('error', 'Session expired, please try again.');
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            // Clear OTP session just in case
            session()->forget(['otp', 'otp_email']);

            return redirect()->route('login')->with('success', 'Password reset successfully!');
        }

        return redirect()->route('login')->with('error', 'User not found.');
    }
}
