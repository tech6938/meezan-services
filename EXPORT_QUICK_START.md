# Export Feature - Quick Start Guide

## Installation

The export feature has been implemented and is ready to use. No additional installation needed beyond what's already done.

## Quick Usage

### 1. In Your Blade Templates

To add an export button to any list page:

```php
@include('components.export-button', [
    'apiUrl' => route('users.export'),      // Required: API endpoint
    'fileName' => 'users_export',           // Required: Base filename
    'queryParams' => request()->all(),      // Optional: Current filters
    'buttonLabel' => 'Export to Excel'      // Optional: Button text
])
```

### 2. Example Implementations

#### Users Export
```php
@include('components.export-button', [
    'apiUrl' => route('users.export'),
    'fileName' => 'users_' . now()->format('Y-m-d'),
    'queryParams' => [
        'search' => request('search'),
        'status' => request('status'),
    ],
    'buttonLabel' => 'Export Users'
])
```

#### Providers by Status
```php
@include('components.export-button', [
    'apiUrl' => route('providers.export'),
    'fileName' => 'approved_providers',
    'queryParams' => array_merge(request()->all(), ['status' => 'approved']),
    'buttonLabel' => 'Export'
])
```

#### Bookings with Date Range
```php
@include('components.export-button', [
    'apiUrl' => route('bookings.exportMulti'),
    'fileName' => 'bookings',
    'queryParams' => [
        'status' => request('status'),
        'start_date' => request('start_date'),
        'end_date' => request('end_date'),
    ],
    'buttonLabel' => 'Download Excel'
])
```

## Available API Endpoints

| Module | Endpoint | Route Name |
|--------|----------|-----------|
| Users | `/users/export` | `users.export` |
| Providers | `/providers/export` | `providers.export` |
| Bookings | `/bookings/export` | `bookings.exportMulti` |
| Requests | `/requests/export` | `requests.export` |

## Query Parameters

### All Exports Support:
- `search` - Search by name/email/title
- `status` - Filter by status
- `start_date` - From date (YYYY-MM-DD)
- `end_date` - To date (YYYY-MM-DD)
- `sort_by` - Column to sort by
- `sort_order` - 'asc' or 'desc'

### Status Values:
- **Users**: `blocked`, `unblocked`
- **Providers**: `approved`, `blocked`, `suspend`, `pending`
- **Bookings**: `pending`, `in_progress`, `complete_booking`, `cancel`
- **Requests**: `pending`, `approved`

## Features

✅ **Automatic Filtering**
- Search filters
- Status filters
- Date range filters

✅ **Automatic Sorting**
- By any column
- Ascending or descending

✅ **Professional Formatting**
- Bold headers
- Proper column widths
- Date formatting
- Green header color

✅ **User Feedback**
- Loading spinner
- Success/error messages
- Timestamp in filename

## Excel Output

Each export includes:
- Module name and timestamp in filename
- Bold green header row
- Formatted dates
- All visible table columns
- Proper column widths

Example filename: `users_2026-01-20_14-30-45.xlsx`

## Pages with Export Already Implemented

### Users Module
- ✅ Users List

### Providers Module
- ✅ Approved Providers
- ✅ Blocked Providers
- ✅ Suspended Providers
- ✅ Pending Providers

### Bookings Module
- ✅ All Bookings
- ✅ Pending Bookings
- ✅ In Progress Bookings
- ✅ Completed Bookings
- ✅ Cancelled Bookings

### Service Requests Module
- ✅ All Requests
- ✅ Pending Requests
- ✅ Approved Requests

## Adding Export to New Pages

To add export functionality to a new page:

1. **Add component to your Blade template:**
```php
<div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
    @include('components.export-button', [
        'apiUrl' => route('module.export'),
        'fileName' => 'module_name',
        'queryParams' => request()->all(),
        'buttonLabel' => 'Export'
    ])
</div>
```

2. **Create export class** (if new module):
```php
// app/Exports/YourModuleExport.php
public static function fromRequest(Request $request)
{
    $query = YourModel::query();
    // Apply filters here
    return new self($query->get());
}
```

3. **Add export method to controller:**
```php
public function exportYourModule(Request $request)
{
    return Excel::download(
        YourModuleExport::fromRequest($request),
        'module_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
    );
}
```

4. **Register route:**
```php
Route::get('/module/export', 'exportYourModule')->name('module.export');
```

## Customization

### Change Button Label
```php
'buttonLabel' => 'Download as Excel'
```

### Change File Name
```php
'fileName' => 'my_custom_name'
```

### Modify Filters
```php
'queryParams' => [
    'status' => 'active',
    'department' => 'sales',
    'created_after' => '2026-01-01'
]
```

## Troubleshooting

**Export not working?**
- Check browser console (F12) for errors
- Verify the API endpoint is correct
- Ensure you're authenticated

**Data not matching filters?**
- Check if filters are being passed correctly
- Verify status values are correct
- Check the SQL query in browser Network tab

**File won't download?**
- Check browser download settings
- Clear browser cache and try again
- Check file size (if extremely large)

## Performance Tips

1. **Use specific date ranges** - Faster than exporting all historical data
2. **Use status filters** - Reduces dataset size
3. **Use search filters** - Narrows down results

## Browser Compatibility

Works on all modern browsers:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Next Steps

- Review full documentation: `EXPORT_FEATURE_DOCUMENTATION.md`
- Check implementation in any of the existing pages
- Customize styling as needed
- Add to your new modules
