<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nairobi Waters Complaint Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; }
        .form-container { background: rgba(255, 255, 255, 0.95); border-radius: 15px; padding: 2rem; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); }
        .form-group label { font-weight: bold; color: #2c3e50; }
        .btn-primary { background: #667eea; border: none; }
        .btn-primary:hover { background: #764ba2; }
        .char-counter { font-size: 0.9rem; color: #6c757d; }
        #loadingModal .modal-content { background: rgba(255, 255, 255, 0.9); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-container">
                    <h2 class="text-center mb-4 text-primary"><i class="fas fa-water me-2"></i>Nairobi Waters Complaint Form</h2>
                    <form id="complaintForm" action="http://localhost:5001/analyze" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                        <input type="hidden" name="user_email" value="{{ auth()->user()->email }}">
                        <input type="hidden" name="user_name" value="{{ auth()->user()->name }}">

                        <div class="form-group mb-3">
                            <label for="user_name">Name:</label>
                            <input type="text" class="form-control" id="user_name" value="{{ auth()->user()->name }}" disabled>
                        </div>

                        <div class="form-group mb-3">
                            <label for="user_email">Email:</label>
                            <input type="email" class="form-control" id="user_email" value="{{ auth()->user()->email }}" disabled>
                        </div>

                        <div class="form-group mb-3">
                            <label for="user_phone">Phone Number:</label>
                            <input type="tel" class="form-control" id="user_phone" name="user_phone" placeholder="Enter phone number" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="subcounty">Subcounty:</label>
                            <select class="form-control" id="subcounty" name="subcounty" required>
                                <option value="">Select Subcounty</option>
                                <option value="Dagoretti North">Dagoretti North</option>
                                <option value="Dagoretti South">Dagoretti South</option>
                                <option value="Embakasi Central">Embakasi Central</option>
                                <!-- Add more subcounties -->
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="ward">Ward:</label>
                            <select class="form-control" id="ward" name="ward" required>
                                <option value="">Select Ward</option>
                                <option value="Kilimani">Kilimani</option>
                                <option value="Kawangware">Kawangware</option>
                                <option value="Gatina">Gatina</option>
                                <!-- Add more wards -->
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="complaint">Complaint:</label>
                            <textarea class="form-control" id="complaint" name="complaint" rows="5" maxlength="500" required></textarea>
                            <div class="char-counter text-end" id="charCount">0/500</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="frequency">Frequency:</label>
                            <select class="form-control" id="frequency" name="frequency" required>
                                <option value="First time">First time</option>
                                <option value="Daily">Daily</option>
                                <option value="Weekly">Weekly</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="entity_type">Entity Type:</label>
                            <select class="form-control" id="entity_type" name="entity_type" required>
                                <option value="Individual">Individual</option>
                                <option value="Estate">Estate</option>
                                <option value="School">School</option>
                                <option value="Hospital">Hospital</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label for="entity_name">Entity Name:</label>
                            <input type="text" class="form-control" id="entity_name" name="entity_name" placeholder="Enter entity name (if applicable)">
                        </div>

                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane me-2"></i>Submit Complaint</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-body p-4">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <h5>Submitting your complaint... Please wait.</h5>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#complaint').on('input', function() {
                let length = $(this).val().length;
                $('#charCount').text(`${length}/500`);
                if (length > 500) {
                    $('#charCount').addClass('text-danger');
                } else {
                    $('#charCount').removeClass('text-danger');
                }
            });

            $('#complaintForm').on('submit', function(e) {
                e.preventDefault();
                $('#loadingModal').modal('show');

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#loadingModal').modal('hide');
                        alert('Complaint submitted successfully!');
                        window.location.href = "{{ route('customer.dashboard') }}";
                    },
                    error: function(xhr) {
                        $('#loadingModal').modal('hide');
                        let errorMsg = xhr.responseJSON?.error || 'An error occurred while submitting your complaint.';
                        alert(errorMsg);
                    }
                });
            });
        });
    </script>
</body>
</html>