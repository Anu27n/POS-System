@extends('installer.layout')

@section('title', 'Database')
@section('subtitle', 'Step 3: Database Configuration')
@section('show-steps', true)
@section('step1-class', 'completed')
@section('line1-class', 'completed')
@section('step2-class', 'completed')
@section('line2-class', 'completed')
@section('step3-class', 'active')

@section('content')
<h5 class="mb-4">Database Configuration</h5>

<form action="{{ route('installer.database.store') }}" method="POST">
    @csrf
    
    <div class="mb-3">
        <label class="form-label">Database Type</label>
        <select class="form-select" name="db_connection" id="dbConnection" required>
            <option value="sqlite">SQLite (Easiest - Recommended for testing)</option>
            <option value="mysql">MySQL / MariaDB</option>
            <option value="pgsql">PostgreSQL</option>
        </select>
        <small class="text-muted">SQLite is the easiest option and requires no additional configuration.</small>
    </div>
    
    <div id="connectionFields" style="display: none;">
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label">Host</label>
                    <input type="text" class="form-control" name="db_host" value="127.0.0.1">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Port</label>
                    <input type="text" class="form-control" name="db_port" value="3306" id="dbPort">
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Database Name</label>
            <input type="text" class="form-control" name="db_database" placeholder="pos_system">
            <small class="text-muted">The database must already exist.</small>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" name="db_username" placeholder="root">
        </div>
        
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="db_password" placeholder="Leave empty if none">
        </div>
    </div>
    
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('installer.license') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <button type="submit" class="btn btn-primary">
            Test Connection & Continue <i class="bi bi-arrow-right ms-2"></i>
        </button>
    </div>
</form>

<script>
document.getElementById('dbConnection').addEventListener('change', function() {
    const fields = document.getElementById('connectionFields');
    const portInput = document.getElementById('dbPort');
    
    if (this.value === 'sqlite') {
        fields.style.display = 'none';
    } else {
        fields.style.display = 'block';
        if (this.value === 'mysql') {
            portInput.value = '3306';
        } else if (this.value === 'pgsql') {
            portInput.value = '5432';
        }
    }
});
</script>
@endsection
