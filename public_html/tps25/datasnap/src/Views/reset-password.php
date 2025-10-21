<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datasnap - Nueva Contraseña</title>
    <link rel="shortcut icon" href="/config/icons/logo-solo.svg">
    <link rel="stylesheet" href="/config/css/register-login.css">
</head>

<body>
    <main>
        <div class="panel-1">
            <div class="logo">
                <svg width="95" height="71" viewBox="0 0 80 50" fill="currentcolor" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_226_52)">
                        <path d="M22.4142 7.791V15.3148L27.6024 19.6005L32.7906 23.8862V26.6481V29.4418H38.6321C44.2046 29.4418 44.512 29.41 46.1646 28.6164C49.8924 26.8069 50.392 22.3624 47.1638 19.8545C46.2414 19.1243 43.6666 17.9815 42.9748 18.0132C42.8595 18.0132 40.4384 18.0132 37.5945 18.0132H32.3678L32.4831 13.4735L32.5984 8.96561L38.9395 8.87037C42.8595 8.80688 46.0877 8.90211 47.3944 9.15608C55.5418 10.5847 61.6139 16.8704 61.6139 23.8545C61.6139 28.2672 59.846 31.8862 56.2335 34.8386C52.0445 38.2672 48.3552 39.2831 39.8619 39.2831H34.4431L29.2934 34.9339C26.4495 32.5212 23.7209 30.2672 23.1828 29.8862L22.222 29.1878L22.3373 38.5212L22.4526 47.8545H35.058C49.0085 47.8545 50.7763 47.664 55.8876 45.664C67.6091 41.0291 74.2961 29.9497 71.6444 19.4418C69.5691 11.1243 62.8821 4.55291 53.5818 1.69577C49.2391 0.362431 47.5481 0.235447 34.7121 0.235447H22.4142V7.791Z" fill="currentcolor"/>
                    </g>
                </svg>
                <h2>DATASNAP</h2>
            </div>
            <img class="persona" src="/config/images/undraw_attached-file_j0t2-removebg-preview 1.png">
        </div>
        <div class="panel-2">
            <img src="/config/icons/wave.svg" class="wave">
            <h1 class="login-titulo">Nueva Contraseña</h1>

            <?php if (!empty($successMessage)): ?>
            <div class="success-message" style="
                background-color: #efe;
                border: 1px solid #cfc;
                color: #363;
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 20px;
                text-align: center;
                font-family: 'DM Sans', sans-serif;
                font-size: 14px;
            ">
                <?php echo htmlspecialchars($successMessage); ?>
                <br><a href="/login" style="color: #2DACB5;">Ir al login</a>
            </div>
            <?php else: ?>

            <form class="formulario" method="post">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                
                <label for="password">Nueva Contraseña:</label>
                <div class="input-grupo">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-lock-fill" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 0a4 4 0 0 1 4 4v2.05a2.5 2.5 0 0 1 2 2.45v5a2.5 2.5 0 0 1-2.5 2.5h-7A2.5 2.5 0 0 1 2 13.5v-5a2.5 2.5 0 0 1 2-2.45V4a4 4 0 0 1 4-4m0 1a3 3 0 0 0-3 3v2h6V4a3 3 0 0 0-3-3"/>
                        </svg>
                    </span>
                    <input type="password" id="password" name="password" placeholder="Ingrese nueva contraseña" required>
                </div>

                <label for="confirm_password">Confirmar Contraseña:</label>
                <div class="input-grupo">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-lock-fill" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 0a4 4 0 0 1 4 4v2.05a2.5 2.5 0 0 1 2 2.45v5a2.5 2.5 0 0 1-2.5 2.5h-7A2.5 2.5 0 0 1 2 13.5v-5a2.5 2.5 0 0 1 2-2.45V4a4 4 0 0 1 4-4m0 1a3 3 0 0 0-3 3v2h6V4a3 3 0 0 0-3-3"/>
                        </svg>
                    </span>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirme nueva contraseña" required>
                </div>

                <center>
                    <button type="submit" class="submit-boton">Actualizar Contraseña</button>
                </center>
            </form>

            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
            <div class="error-message" style="
                background-color: #fee;
                border: 1px solid #fcc;
                color: #c33;
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 20px;
                text-align: center;
                font-family: 'DM Sans', sans-serif;
                font-size: 14px;
            ">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>