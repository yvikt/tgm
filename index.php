<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>bot control panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <span style="border: red 1px solid; padding: 3px;"><?php echo explode('/tgm', (explode('?', $_SERVER['REQUEST_URI'])[0]))[1]; ?></span>
    BOT CONTROL
  </header>
    <main>
    <nav>
      <?php include 'templates/nav.php'; ?>
    </nav>
      <section>
        <div class="container">
          <?php include 'main.php'; ?>
        </div>
      </section>
    </main>
  <footer>
    <span>© 2020 Viktor Yakovenko</span>
  </footer>
<div class="tgm"></div>
</body>
</html>
