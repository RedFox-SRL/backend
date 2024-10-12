<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar Contraseña - TrackMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #8b5cf6, #6d28d9);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }
        h2 {
            color: #5b21b6;
            text-align: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        p {
            color: #4b5563;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 500;
            font-size: 0.9rem;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 1rem;
        }
        input[type="email"]:read-only {
            background-color: #f3f4f6;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #8b5cf6;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        button:hover {
            background-color: #7c3aed;
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            .container {
                padding: 1.5rem;
            }
            h2 {
                font-size: 1.25rem;
            }
            p {
                font-size: 0.8rem;
            }
            input[type="email"],
            input[type="password"] {
                font-size: 0.9rem;
            }
            button {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Recuperar Contraseña</h2>
        <p>Ingresa tu nueva contraseña para recuperar tu cuenta</p>

        <form action="{{ url('api/password/reset') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="{{ request()->get('email') }}" readonly>
            </div>

            <div class="form-group">
                <label for="password">Nueva Contraseña</label>
                <input type="password" id="password" name="password" required placeholder="Nueva contraseña">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar Contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Confirmar nueva contraseña">
            </div>

            <input type="hidden" name="token" value="{{ request()->get('token') }}">

            <button type="submit">Restablecer Contraseña</button>
        </form>
    </div>
</body>
</html>
