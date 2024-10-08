<?php
$user_id = $_SESSION['user_id'];
// echo 'dscnxjemfdkfvjncxkndfkcn ' . $role . ' ' . $user_id;

$role = $_SESSION['role'];

// Fetch the username of the logged-in user
// $stmt = mysqli_prepare($connection, "SELECT username FROM testt WHERE id = ?");
// mysqli_stmt_bind_param($stmt, "i", $user_id);
// mysqli_stmt_execute($stmt);
// mysqli_stmt_bind_result($stmt, $username);

// mysqli_stmt_fetch($stmt); // Fetch the result into variables
// mysqli_stmt_close($stmt);
// mysqli_close($connection);
$username = $_SESSION['username'];

?>

<div>

  <div class="sidebar close">
    <div class="logo-details">
      <i class='bx bxl-circle'>logo</i>
      <span class="logo_name">Tutorials</span>
    </div>
    <ul class="nav-links">
      <li>
        <a href="dashboard.php">
          <i class='bx bx-grid-alt'></i>
          <span class="link_name">Dashboard</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="#">Dashboard</a></li>
        </ul>
      </li>
      <li>
        <?php
        if ($_SESSION['role'] == 'SuperAdmin' || $_SESSION['role'] == 'Admin') {
          echo '
        <div class="iocn-link">
          <a href="#">
            <i class="bx bx-collection"></i>
            <span class="link_name">Category Settings</span>
          </a>
          <i class="bx bxs-chevron-down arrow"></i>
        </div>
        <ul class="sub-menu">
          <li><a class="link_name" href="">Category Settings</a></li>
          <li><a href="manage_categories.php">Manage Categories</a></li>
          <li><a href="./AddCategories.php">Create a new category</a></li>
        </ul> 
      </li>
      <li>
      ';
        }

        ?>
        <div class="iocn-link">
          <a href="#">
            <i class='bx bx-book-alt'></i>
            <span class="link_name">Manage Posts</span>
          </a>
          <i class='bx bxs-chevron-down arrow'></i>
        </div>
        <ul class="sub-menu">
          <li><a class="link_name" href="#">Manage Posts</a></li>
          <li><a href="copyallp.php">All Posts</a></li>
          <li><a href="AddPost.php">Add Post</a></li>
          <?php
          if ($_SESSION['role'] == 'SuperAdmin') {
            echo '
          <li><a href="Allcomments.php">User Comments</a></li>
          ';
          }
          ?>
        </ul>
      </li>

      <!-- <li> -->
      <?php
      if ($_SESSION['role'] == 'SuperAdmin') {
        echo '
      <li>
        <div class="iocn-link">
          <a href="#">
            <i class="bx bx-user"></i>
            <span class="link_name">Users Settings</span>
          </a>
          <i class="bx bxs-chevron-down arrow"></i>
        </div>
        <ul class="sub-menu">
          <li><a class="link_name" href="#">Users Settings</a></li>
          <li><a href="manage.php">Manage Users</a></li>
          <li><a href="create-user.php">Create a User</a></li>
          <li><a href="Application-users.php">Application Users</a></li>
        </ul>
      </li>
      ';
      }
      ?>


      <?php
      if ($_SESSION['role'] == 'SuperAdmin') {
        echo '
   <li>
        <div class="iocn-link">
          <a href="#">
            <i class="bx bx-cog"></i>
            <span class="link_name">Settings</span>
          </a>
          <i class="bx bxs-chevron-down arrow"></i>
        </div>
        <ul class="sub-menu">
          <li><a class="link_name" href="#">Settings</a></li>
          <li><a href="logo-settings.php">Logo</a></li>
          <li><a href="navlink-settings.php">Navigation Links Settings</a></li>
        </ul>
      </li>
      ';
      }
      ?>

      <li>
        <a href="logout.php">
          <!-- <i class="bx bx-user"></i> -->
          <i class='bx bx-log-out'></i>
          <span class="link_name">Logout</span>
        </a>
        <ul class="sub-menu blank">t
          <li><a class="link_name" href="#">Logout</a></li>
        </ul>
        </a>
      </li>

      <li>
        <div class="profile-details">

      </li>
      <!-- ?> -->





      <li>
        <div class="profile-details">

          <div class="name-job" style="position: relative; left: 10%;">
            <?php
            // echo $_SESSION['username'];
            
            ?>
            <div class="profile_name"> <?php echo $username ?></div>
            <div class="job"><?php echo $role ?></div>
          </div>
          <a href="logout.php">

            <i class='bx bx-log-out'></i>
          </a>
        </div>
      </li>
    </ul>
  </div>
  <section class="home-section">
    <div class="home-content">
      <i class='bx bx-menu'></i>
    </div>
  </section>
</div>

<script>
  let arrow = document.querySelectorAll(".arrow");
  for (var i = 0; i < arrow.length; i++) {
    arrow[i].addEventListener("click", (e) => {
      let arrowParent = e.target.parentElement.parentElement;//selecting main parent of arrow
      arrowParent.classList.toggle("showMenu");
    });
  }
  let sidebar = document.querySelector(".sidebar");
  let sidebarBtn = document.querySelector(".bx-menu");
  console.log(sidebarBtn);
  sidebarBtn.addEventListener("click", () => {
    sidebar.classList.toggle("close");
  });
</script>
</body>