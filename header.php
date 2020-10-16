  <header class="header js-float-menu">
    <h1 class="title">Connect</h1>

    <div class="menu-trigger js-toggle-sp-menu">
      <span></span>
      <span></span>
      <span></span>
    </div>

    <?php
        if(empty($_SESSION['user_id'])){
      ?>
    <nav class="nav-menu js-toggle-sp-menu-target">
      <ul class="menu">
        <li class="menu-item"><a class="menu-link" href="./index.php">TOP</a></li>
        <li class="menu-item"><a class="menu-link" href="./signup.php">SIGN UP</a></li>
        <li class="menu-item"><a class="menu-link" href="./login.php">LOGIN</a></li>
        <li class="menu-item"><a class="menu-link" href="./index.php#about">ABOUT</a></li>
        <li class="menu-item"><a class="menu-link" href="./index.php#latest case">LATEST CASE</a></li>
        <li class="menu-item"><a class="menu-link" href="./index.php#contact">CONTACT</a></li>
      </ul>
    </nav>
    <?php
          }else{
        ?>
    <nav class="nav-menu js-toggle-sp-menu-target">
      <ul class="menu">
        <li class="menu-item"><a class="menu-link" href="./index.php">TOP</a></li>
        <li class="menu-item"><a class="menu-link" href="./mypage.php">MYPAGE</a></li>
        <li class="menu-item"><a class="menu-link" href="./logout.php">LOGOUT</a></li>
        <li class="menu-item"><a class="menu-link" href="./toollist.php">TOOLLIST</a></li>
        <li class="menu-item"><a class="menu-link" href="./index.php#about">ABOUT</a></li>
        <li class="menu-item"><a class="menu-link" href="./index.php#latest case">LATEST CASE</a></li>
        <li class="menu-item"><a class="menu-link" href="./index.php#contact">CONTACT</a></li>
      </ul>
    </nav>
    <?php
          }
        ?>

  </header>
