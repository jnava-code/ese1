<?php 
    date_default_timezone_set('Asia/Manila'); // Adjust as per your timezone
    // Current date and time (defined globally)
    $current_day = date('l'); // Day of the week (e.g., Sunday)
    $cdate = date('F j, Y'); // Full date (e.g., November 24, 2024)
    $current_date = date('Y-m-d'); // YYYY-MM-DD format
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="shortcut icon" type="x-icon" href="images/icon1.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Time Tracker</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }
        .header {
            background-color: #cc0000;
            color: white;
            text-align: center;
            padding: 10px;
            font-size: 42px;
            position: relative;
            font-weight: bold;
        }
        .clock {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 16px;
            color: white;
        }

        .instruction-and-container {
            display: flex;
            gap: 25px;
            justify-content: center;
            margin-top: 20px;
            width: 100%;
        }

        .instruction {
            width: 40%;
            padding: 20px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .container {
            width: 40%;
            padding: 20px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .container h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%; /* Ensures input doesn't overflow */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box; 
            margin-top: 25px;
        }
        .form-group-buttons {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .form-group-buttons button {
            flex: 1;
            padding: 10px;
            border: none;
            background-color: #ced4da;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .time-active {
            background-color: #d61e1e !important;
        }

        .form-group-buttons button:hover {
            background-color: #c73a3a;
        }
        .message {
            margin-top: 15px;
            text-align: center;
            color: green;
        }
        .error {
            color: red;
            text-align: center;
        }
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .nav-buttons a {
            flex: 1;
            text-align: center;
            text-decoration: none;
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin: 0 5px;
        }
        .nav-buttons a:hover {
            background-color: #0056b3;
        }#clock-container {
    text-align: center; /* Center the clock */
    margin: 20px;       /* Add some spacing around the clock */
}

#clock {
    font-size: 48px;    /* Increase the font size */
    font-weight: bold;  /* Make it bold for better visibility */
    color: #333;        /* Set a nice color for the clock */
    padding: 10px;      /* Add padding for space around the clock */
    border: 2px solid #ddd; /* Optional: Add a border */
    border-radius: 10px;    /* Optional: Make the border rounded */
    background-color: #f9f9f9; /* Optional: Add a background color */
}

#in-or-out {
    background-color: none;
    border: none;
    font-weight: 600;
    font-size: 32px;
    width: 100%;
    text-align: center;
}

.input-and-msg {
    display: flex;
    gap: 5px;
    flex-direction: column;
    align-items: center;
}

#message {
    color: green;
}
    </style>
     <script>
        function updateClock() {
    const now = new Date();
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const amPm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12 || 12;
    const timeString = `${hours}:${minutes}:${seconds} ${amPm}`;
    document.getElementById('clock').innerText = timeString;
}
setInterval(updateClock, 1000);
window.onload = updateClock; 
    </script>
</head>
<body>
    
<div class="header">
    ESE-Tech Industrial Solutions Corporation - Time Tracker
    <div id="clock-container">
    <span id="clock"></span>
</div>
    <div class="date-info">
        Today is <strong><?php echo $current_day; ?></strong>, 
        <strong><?php echo $cdate; ?></strong> <br>
    </div>
</div>

<div class="instruction-and-container">
    <div class="instruction">
        <h2>Instructions:</h2>
        <div class="step-one">
            <h3>Select Your Attendance Action:</h3>
            <ul>
                <li>Click "TIME IN" if you are starting your morning shift (8:00 AM to 12:00 PM)</li>
                <li>Click "TIME OUT" if you are ending your afternoon shift (1:00 PM to 5:00 PM)</li>
            </ul>
        </div>

        <div class="step-two">
            <h3>Enter Your Employee ID:</h3>
            <ul>
                <li>Locate the text box below the buttons</li>
                <li>Type in your employee ID</li>
            </ul>
        </div>

        <div class="step-three">
            <h3>Submit your Attendance:</h3>
            <ul>
                <li>After entering your employee ID, press the "Enter" key on your keyboard</li>
            </ul>
        </div>
    </div>
    <div class="container">
        <h1>Employee Time Tracker</h1>
        <form method="POST" class="time-form">
                <div class="time-buttons">
                    <div class="form-group-buttons">
                        <button type="button" id="in-button" class="time-button time-active" value="IN">TIME IN</button>
                        <button type="button" id="out-button" class="time-button" value="OUT">TIME OUT</button>
                    </div>
                </div>
                <!-- <input id="in-or-out" name="in-or-out" value="IN" readonly> -->
                <div class="form-group input-and-msg">
                    <!-- <label for="employee_id">Employee ID</label> -->
                    <input type="text" id="employee_id" name="employee_id" placeholder="Enter your Employee ID" autofocus>
                    <span id="message"></span>
                </div>
      
            <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
            <?php if (!empty($success)) echo "<div class='message'>$success</div>"; ?>
        </form>
    </div>
</div>

<script>
    const employeeId = document.getElementById("employee_id");
    const message = document.getElementById("message");
    const formGroupButtons = document.querySelector(".form-group-buttons");
    const timeButtons = document.querySelectorAll(".time-button");
    console.log(message);
    
    formGroupButtons.addEventListener("click", e => {
        const clicked = e.target.closest("button");
        if(!clicked) return;

        timeButtons.forEach(button => {
            button.classList.remove("time-active");
        });
        clicked.classList.add("time-active");
    })

    employeeId.addEventListener("input", e => {
        const value = e.target.value;
        if (value === "+") {
            timeButtons.forEach(button => {
                button.classList.contains("time-active") 
                ? button.classList.remove("time-active") 
                : button.classList.add("time-active");
            
                employeeId.value = "";
            });
        }
    });

employeeId.addEventListener("keydown", function(e) {
    if (e.key === "Enter") { 
        e.preventDefault();
        
        timeButtons.forEach(button => {
            const inOrOut = button.classList.contains("time-active") && button.value;
            const employeeIdValue = employeeId.value;

            const formData = new FormData();
            formData.append("employee_id", employeeIdValue);
            formData.append("in-or-out", inOrOut);

            fetch('employee_time', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
            console.log(data);
            
                if (data.success) {
                    message.innerHTML = data.success;
                    employeeId.value = '';
                    setTimeout(() => {
                        message.innerHTML = '';
                    }, 2000);
                } else if (data.error) {
                    message.innerHTML = data.error;
                    employeeId.value = '';
                    setTimeout(() => {
                        message.innerHTML = '';
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
});

document.body.addEventListener("click", function() {
            document.getElementById("employee_id").focus();
        });

</script>
</body>
</html>