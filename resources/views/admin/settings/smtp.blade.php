@extends('layouts.admin')

@section('title', 'SMTP & Email Settings')
@section('page-title', 'SMTP & Email Settings')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>SMTP Configuration</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.smtp.update') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Mail Driver</label>
                            <select class="form-select" name="mail_mailer">
                                <option value="smtp" {{ $settings['mail_mailer'] == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="sendmail" {{ $settings['mail_mailer'] == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="log" {{ $settings['mail_mailer'] == 'log' ? 'selected' : '' }}>Log (for testing)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Encryption</label>
                            <select class="form-select" name="mail_encryption">
                                <option value="tls" {{ $settings['mail_encryption'] == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ $settings['mail_encryption'] == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="null" {{ $settings['mail_encryption'] == 'null' || !$settings['mail_encryption'] ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" class="form-control" name="mail_host" 
                                value="{{ $settings['mail_host'] }}" placeholder="smtp.gmail.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Port</label>
                            <input type="text" class="form-control" name="mail_port" 
                                value="{{ $settings['mail_port'] }}" placeholder="587">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">SMTP Username</label>
                            <input type="text" class="form-control" name="mail_username" 
                                value="{{ $settings['mail_username'] }}" placeholder="your-email@gmail.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SMTP Password</label>
                            <input type="password" class="form-control" name="mail_password" 
                                value="{{ $settings['mail_password'] }}" placeholder="••••••••">
                            <small class="text-muted">For Gmail, use App Password</small>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3">Sender Information</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">From Email Address</label>
                            <input type="email" class="form-control" name="mail_from_address" 
                                value="{{ $settings['mail_from_address'] }}" placeholder="noreply@yourstore.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">From Name</label>
                            <input type="text" class="form-control" name="mail_from_name" 
                                value="{{ $settings['mail_from_name'] }}" placeholder="Your Store Name">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3">Notification Preferences</h6>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="notifications_enabled" 
                                id="notificationsEnabled" value="1" {{ $settings['notifications_enabled'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="notificationsEnabled">
                                <strong>Enable Email Notifications</strong>
                            </label>
                        </div>
                    </div>

                    <div class="ms-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="notify_new_order" 
                                id="notifyNewOrder" value="1" {{ $settings['notify_new_order'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="notifyNewOrder">
                                Notify store owner when new order is received
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="notify_order_status" 
                                id="notifyOrderStatus" value="1" {{ $settings['notify_order_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="notifyOrderStatus">
                                Notify customer when order status changes
                            </label>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Test Email -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-send me-2"></i>Test Email</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">Send a test email to verify your SMTP configuration.</p>
                <div class="mb-3">
                    <input type="email" class="form-control" id="testEmail" placeholder="Enter test email address">
                </div>
                <button type="button" class="btn btn-outline-primary w-100" onclick="sendTestEmail()">
                    <i class="bi bi-envelope me-1"></i> Send Test Email
                </button>
                <div id="testResult" class="mt-3"></div>
            </div>
        </div>

        <!-- Common SMTP Settings -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Common SMTP Settings</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Gmail SMTP</strong>
                    <ul class="small text-muted mb-0">
                        <li>Host: smtp.gmail.com</li>
                        <li>Port: 587 (TLS) or 465 (SSL)</li>
                        <li>Use App Password (not regular password)</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <strong>Outlook/Office365</strong>
                    <ul class="small text-muted mb-0">
                        <li>Host: smtp.office365.com</li>
                        <li>Port: 587</li>
                        <li>Encryption: TLS</li>
                    </ul>
                </div>
                <div>
                    <strong>SendGrid</strong>
                    <ul class="small text-muted mb-0">
                        <li>Host: smtp.sendgrid.net</li>
                        <li>Port: 587</li>
                        <li>Username: apikey</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function sendTestEmail() {
    const email = document.getElementById('testEmail').value;
    const resultDiv = document.getElementById('testResult');
    
    if (!email) {
        resultDiv.innerHTML = '<div class="alert alert-warning small">Please enter an email address</div>';
        return;
    }
    
    resultDiv.innerHTML = '<div class="text-center"><span class="spinner-border spinner-border-sm"></span> Sending...</div>';
    
    fetch('{{ route("admin.settings.smtp.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ test_email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<div class="alert alert-success small"><i class="bi bi-check-circle me-1"></i>' + data.message + '</div>';
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger small"><i class="bi bi-x-circle me-1"></i>' + data.message + '</div>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="alert alert-danger small">Network error. Please try again.</div>';
    });
}
</script>
@endpush
@endsection
