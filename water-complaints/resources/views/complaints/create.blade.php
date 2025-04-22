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
    </style>
</head>
<body>
    <div class="container">
        <h1>Nairobi Waters Complaint Form</h1>
        <form id="complaintForm">
            <label for="fullName">Full Name:</label>
            <input type="text" id="fullName" name="full_name" required>

            <label for="contactInfo">Contact Information:</label>
            <input type="text" id="contactInfo" name="contact_info" required>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="subcounty">Subcounty:</label>
            <select id="subcounty" name="subcounty" required>
                <option value="">Select Subcounty</option>
                <option value="Westlands">Westlands</option>
                <option value="Kibra">Kibra</option>
                <option value="Starehe">Starehe</option>
                <!-- Add more subcounties as needed -->
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

    <script>
        document.getElementById('complaintForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData);

            fetch('http://localhost:5001/submit-complaint', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        // Example function to dynamically update wards based on subcounty selection
        document.getElementById('subcounty').addEventListener('change', function() {
            const subcounty = this.value;
            const wards = {
                'Westlands': ['Kilimani', 'Karen', 'Langata'],
                'Kibra': ['Kibra', 'Laini Saba', 'Makadara'],
                'Starehe': ['Ngara', 'Parklands', 'Highridge']
            };
            const wardSelect = document.getElementById('ward');
            wardSelect.innerHTML = '<option value="">Select Ward</option>';
            wards[subcounty].forEach(ward => {
                const option = document.createElement('option');
                option.value = ward;
                option.textContent = ward;
                wardSelect.appendChild(option);
            });
        });
    </script>
</body>
</html>