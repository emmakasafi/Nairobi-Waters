<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nairobi Waters Complaint Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }

        /* Popup Message Styles */
        .popup-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px 20px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            z-index: 1000;
            display: none;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Nairobi Waters Complaint Form</h1>
        <form id="complaintForm" action="{{ route('complaints.store') }}" method="POST">
            @csrf
            <label for="fullName">Full Name:</label>
            <input type="text" id="fullName" name="full_name" required>

            <label for="userEmail">Email Address:</label>
            <input type="email" id="userEmail" name="user_email" required>

            <label for="userPhone">Phone Number:</label>
            <input type="tel" id="userPhone" name="user_phone" required>

            <label for="subcounty">Subcounty:</label>
            <select id="subcounty" name="subcounty" required>
                <option value="">Select Subcounty</option>
                <!-- Options will be populated dynamically -->
            </select>

            <label for="ward">Ward:</label>
            <select id="ward" name="ward" required>
                <option value="">Select Ward</option>
                <!-- Options will be populated dynamically -->
            </select>

            <label for="complaint">Complaint:</label>
            <textarea id="complaint" name="complaint" required></textarea>

            <label for="dateTime">Date and Time:</label>
            <input type="datetime-local" id="dateTime" name="date_time" required>

            <label for="frequency">Frequency:</label>
            <select id="frequency" name="frequency" required>
                <option value="First time">First time</option>
                <option value="Daily">Daily</option>
                <option value="Weekly">Weekly</option>
                <option value="Monthly">Monthly</option>
                <option value="Other">Other</option>
            </select>

            <label for="severity">Severity:</label>
            <select id="severity" name="severity" required>
                <option value="Minor inconvenience">Minor inconvenience</option>
                <option value="Moderate impact">Moderate impact</option>
                <option value="Major disruption">Major disruption</option>
                <option value="Urgent">Urgent</option>
            </select>

            <label for="entityType">Entity Type:</label>
            <select id="entityType" name="entity_type" required>
                <option value="Individual">Individual</option>
                <option value="Estate">Estate</option>
                <option value="School">School</option>
                <option value="Hospital">Hospital</option>
                <option value="Other">Other</option>
            </select>

            <label for="entityName">Entity Name:</label>
            <input type="text" id="entityName" name="entity_name">

            <button type="submit">Submit Complaint</button>
        </form>
    </div>

    <!-- Popup Message -->
    <div id="popupMessage" class="popup-message"></div>

    <script>
        // Load subcounties on page load
        window.addEventListener('DOMContentLoaded', () => {
            fetch('/get-subcounties')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Subcounties:', data); // Log subcounty data
                    const subcountySelect = document.getElementById('subcounty');
                    subcountySelect.innerHTML = '<option value="">Select Subcounty</option>';
                    if (data.length > 0) {
                        data.forEach(subcounty => {
                            const option = document.createElement('option');
                            option.value = subcounty.subcounty;
                            option.textContent = subcounty.subcounty;
                            subcountySelect.appendChild(option);
                        });
                    } else {
                        subcountySelect.innerHTML = '<option value="">No subcounties found</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading subcounties:', error);
                });
        });

        // Load wards based on selected subcounty
        document.getElementById('subcounty').addEventListener('change', function () {
            const selectedSubcounty = this.value;
            const wardSelect = document.getElementById('ward');
            wardSelect.innerHTML = '<option value="">Loading wards...</option>';

            if (selectedSubcounty) {
                fetch(`/get-wards/${encodeURIComponent(selectedSubcounty)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Wards for selected subcounty:', data); // Log ward data
                        wardSelect.innerHTML = '<option value="">Select Ward</option>';
                        if (data.length > 0) {
                            data.forEach(ward => {
                                const option = document.createElement('option');
                                option.value = ward;
                                option.textContent = ward;
                                wardSelect.appendChild(option);
                            });
                        } else {
                            wardSelect.innerHTML = '<option value="">No wards found</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading wards:', error);
                        wardSelect.innerHTML = '<option value="">Failed to load wards</option>';
                    });
            }
        });

        document.getElementById('complaintForm').addEventListener('submit', function(event) {
            event.preventDefault();
            console.log("Form submission started");

            const popupMessage = document.getElementById('popupMessage');
            popupMessage.classList.remove('success', 'error');
            popupMessage.style.display = 'none';

            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData);
            console.log("Form data:", data);

            // Ensure the complaint field is included
            if (!data.complaint) {
                popupMessage.classList.add('error');
                popupMessage.textContent = 'Complaint text is required';
                popupMessage.style.display = 'block';
                setTimeout(() => {
                    popupMessage.style.display = 'none';
                }, 5000);
                return;
            }

            fetch('http://localhost:5001/analyze', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ complaint: data.complaint })
            })
            .then(response => {
                console.log("Response received:", response);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log("Data received:", data);
                if (data.error) {
                    popupMessage.classList.add('error');
                    popupMessage.textContent = data.error;
                } else {
                    popupMessage.classList.add('success');
                    popupMessage.textContent = `Complaint submitted successfully\nSentiment: ${data.sentiment}\nCategory: ${data.category}`;
                }
                popupMessage.style.display = 'block';
                setTimeout(() => {
                    popupMessage.style.display = 'none';
                }, 5000);
            })
            .catch(error => {
                console.error('Error:', error);
                popupMessage.classList.add('error');
                popupMessage.textContent = 'An error occurred while submitting the complaint: ' + error.message;
                popupMessage.style.display = 'block';
                setTimeout(() => {
                    popupMessage.style.display = 'none';
                }, 5000);
            });
        });
    </script>
</body>
</html>