<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анализатор страниц</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>

<body class="min-vh-100 d-flex flex-column">
    <header class="flex-shrink-0">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark px-3">
            <a class="navbar-brand" href="/">Анализатор страниц</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="/">Главная</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/urls">Сайты</a>
                    </li>

                </ul>
            </div>
        </nav>
    </header>

    <?php if (array_key_exists('success', $flash ?? [])) : ?>
        <div class="alert alert-success" role="alert">
            <?php foreach ($flash['success'] as $message) {
                echo $message . '<br>';
            }
            ?>
        </div>
    <?php endif; ?>

    <?php if (array_key_exists('warning', $flash ?? [])) : ?>
        <div class="alert alert-warning" role="alert">
            <?php foreach ($flash['warning'] as $message) {
                echo $message . '<br>';
            }
            ?>
        </div>
    <?php endif; ?>

    <?php if (array_key_exists('error', $flash ?? [])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($flash['error'] as $message) {
                echo $message . '<br>';
            }
            ?>
        </div>
    <?php endif; ?>

    <?= $content ?? '' ?>
    <footer class="border-top py-3 mt-5 flex-shrink-0">
        <div class="container-lg">
            <div class="text-body-secondary mt-4 mt-md-auto">
                ©
                <a class="text-decoration-none text-body-secondary" href="https://github.com/pozys" target="_blank">pozys</a>,
                <?php echo date('Y') ?>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>

</html>