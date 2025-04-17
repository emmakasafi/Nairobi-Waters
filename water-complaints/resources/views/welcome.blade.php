<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nairobi Water Complaints Analysis</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #A0D8F3; /* Light blue */
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-image: url('path-to-water-background.jpg');
            background-size: cover;
            background-position: center;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent white */
            border-radius: 8px;
            box-shadow: 0 4px 8px 12px rgba(0, 0, 0, 0.1);
            margin-top: 50px; /* Adjust vertical position */
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 2.2em; /* Larger font size */
            font-weight: bold; /* Bold font weight */
            color: #0077B6; /* Deep blue */
            margin-bottom: 10px; /* Space below the title */
        }
        .header p {
            font-size: 1.1em; /* Slightly larger font size */
            color: #555; /* Darker text color */
        }
        .card {
            padding: 30px;
            border-radius: 8px;
            background-color: #E6F7FF; /* Light blue */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .button {
            background-color: #0077B6; /* Deep blue */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: inline-block;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #005f7a; /* Darker blue */
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px; /* Add space below the grid */
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Nairobi Water Complaints Analysis</h1>
            <p>Welcome to our system for analyzing water complaints in Nairobi</p>
        </div>
        <div class="grid">
            <div class="card">
                <h2 class="text-xl font-semibold text-gray-900">Submit a Complaint</h2>
                <p class="mt-4 text-gray-600 text-sm leading-relaxed">
                    Easily submit your water-related complaints through our user-friendly interface. We ensure your concerns are heard and addressed promptly.
                </p>
                <a href="{{ route('register') }}" class="button">Get Started</a>
            </div>
            <div class="card">
                <h2 class="text-xl font-semibold text-gray-900">View Complaint Status</h2>
                <p class="mt-4 text-gray-600 text-sm leading-relaxed">
                    Track the status of your submitted complaints. Our system provides real-time updates to keep you informed about the resolution process.
                </p>
                <button class="button" onclick="showLoginOptions()">Login</button>
            </div>
            <div class="card">
                <h2 class="text-xl font-semibold text-gray-900">Register</h2>
                <p class="mt-4 text-gray-600 text-sm leading-relaxed">
                    Sign up to start submitting and tracking water complaints.
                </p>
                <a href="{{ route('register') }}" class="button">Register</a>
            </div>
        </div>
    </div>

    <div id="login-options" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 items-center justify-center">
        <div class="bg-white p-8 rounded shadow-md w-96">
            <h2 class="text-2xl font-semibold mb-6">Login Options</h2>
            <a href="{{ route('admin.login') }}" class="button block mb-4">Login as Admin</a>
            <a href="{{ route('customer.login') }}" class="button block">Login as Customer</a>
        </div>
    </div>

    <script>
        function showLoginOptions() {
            document.getElementById('login-options').classList.toggle('hidden');
        }
    </script>
</body>
</html>