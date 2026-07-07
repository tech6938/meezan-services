@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Referral Settings</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('referrals.settings.update') }}" method="POST" class="row">
                                @csrf

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Enable Referral System</label>
                                    <select name="referral_enabled" class="form-control">
                                        <option value="1" {{ (int) ($settings->referral_enabled ?? 0) === 1 ? 'selected' : '' }}>Enabled</option>
                                        <option value="0" {{ (int) ($settings->referral_enabled ?? 0) === 0 ? 'selected' : '' }}>Disabled</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Commission Type</label>
                                    <select name="referral_type" class="form-control">
                                        <option value="percentage" {{ ($settings->referral_type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                        <option value="fixed" {{ ($settings->referral_type ?? 'percentage') === 'fixed' ? 'selected' : '' }}>Fixed</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Level 1 Commission</label>
                                    <input type="number" step="0.01" name="referral_level_1" class="form-control"
                                        value="{{ old('referral_level_1', $settings->referral_level_1 ?? 0) }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Minimum Reward Limit</label>
                                    <input type="number" step="0.01" name="referral_min_amount" class="form-control"
                                        value="{{ old('referral_min_amount', $settings->referral_min_amount ?? '') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Maximum Reward Limit</label>
                                    <input type="number" step="0.01" name="referral_max_amount" class="form-control"
                                        value="{{ old('referral_max_amount', $settings->referral_max_amount ?? '') }}">
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
