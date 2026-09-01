<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staf Login - PIN Access</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Custom Vanilla CSS -->
    <style>
        :root {
            --primary: hsl(28, 48%, 48%);
            --primary-light: hsl(28, 50%, 60%);
            --background: hsl(210, 24%, 8%);
            --surface: hsl(210, 24%, 12%);
            --text-main: hsl(210, 20%, 95%);
            --text-muted: hsl(210, 14%, 65%);
            --error: hsl(0, 75%, 50%);
            --radius: 16px;
            --shadow: 0 10px 30px rgba(0,0,0,0.5);
            --transition: all 0.2s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
        }

        .login-card {
            background: var(--surface);
            padding: 2.5rem 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 380px;
            text-align: center;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .header p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        /* PIN Bullets Display */
        .pin-dots {
            display: flex;
            justify-content: center;
            gap: 1.2rem;
            margin-bottom: 2rem;
        }

        .dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid var(--text-muted);
            transition: var(--transition);
        }

        .dot.active {
            background: var(--primary);
            border-color: var(--primary);
            transform: scale(1.2);
            box-shadow: 0 0 8px var(--primary);
        }

        /* Keypad Grid */
        .keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            max-width: 280px;
            margin: 0 auto;
        }

        .key {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--text-main);
            font-size: 1.6rem;
            font-weight: 600;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
            user-select: none;
            transition: var(--transition);
        }

        .key:active {
            background: var(--primary);
            color: white;
            transform: scale(0.92);
        }

        .key.clear {
            font-size: 1rem;
            text-transform: uppercase;
            font-weight: 800;
            color: var(--error);
        }

        .key.backspace {
            font-size: 1.5rem;
        }

        /* Error Notification */
        .error-message {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 1rem;
            font-weight: 600;
            min-height: 20px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="header">
            <h1>Akses Staf</h1>
            <p>Masukkan 6 digit PIN Anda untuk masuk</p>
        </div>

        <!-- PIN display dots -->
        <div class="pin-dots" id="dots-container">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>

        <!-- Hidden Form for Submit -->
        <form action="/login-pin" method="POST" id="pin-form">
            @csrf
            <input type="hidden" name="pin" id="pin-input">
        </form>

        <!-- Keypad -->
        <div class="keypad">
            <button class="key" onclick="pressKey('1')">1</button>
            <button class="key" onclick="pressKey('2')">2</button>
            <button class="key" onclick="pressKey('3')">3</button>
            
            <button class="key" onclick="pressKey('4')">4</button>
            <button class="key" onclick="pressKey('5')">5</button>
            <button class="key" onclick="pressKey('6')">6</button>
            
            <button class="key" onclick="pressKey('7')">7</button>
            <button class="key" onclick="pressKey('8')">8</button>
            <button class="key" onclick="pressKey('9')">9</button>
            
            <button class="key clear" onclick="clearPin()">Clear</button>
            <button class="key" onclick="pressKey('0')">0</button>
            <button class="key backspace" onclick="backspacePin()">⌫</button>
        </div>

        <div class="error-message">
            @error('pin')
                {{ $message }}
            @enderror
        </div>
    </div>

    <!-- Keypad Logic -->
    <script>
        let pin = "";
        const maxLen = 6;
        const dots = document.querySelectorAll('.dot');
        const pinInput = document.getElementById('pin-input');
        const pinForm = document.getElementById('pin-form');

        function pressKey(num) {
            if (pin.length < maxLen) {
                pin += num;
                updateDots();
                if (pin.length === maxLen) {
                    submitPin();
                }
            }
        }

        function clearPin() {
            pin = "";
            updateDots();
        }

        function backspacePin() {
            if (pin.length > 0) {
                pin = pin.slice(0, -1);
                updateDots();
            }
        }

        function updateDots() {
            dots.forEach((dot, index) => {
                if (index < pin.length) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function submitPin() {
            pinInput.value = pin;
            // Delay submit sedikit agar user melihat bulatan keenam menyala
            setTimeout(() => {
                pinForm.submit();
            }, 150);
        }

        // Support Physical Keyboard Input
        document.addEventListener('keydown', (e) => {
            if (e.key >= '0' && e.key <= '9') {
                pressKey(e.key);
            } else if (e.key === 'Backspace') {
                backspacePin();
            } else if (e.key === 'Escape') {
                clearPin();
            }
        });
    </script>
</body>
</html>
