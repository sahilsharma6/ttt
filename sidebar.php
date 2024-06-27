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
        <div class="iocn-link">
          <a href="#">
            <i class='bx bx-collection'></i>
            <span class="link_name">Category</span>
          </a>
          <i class='bx bxs-chevron-down arrow'></i>
        </div>
        <ul class="sub-menu">
          <li><a class="link_name" href="">Category</a></li>
          <li><a href="AllCategories.php">Categories</a></li>
          <li><a href="./AddCategories.php">Add Categories</a></li>
        </ul>
      </li>
      <li>
        <div class="iocn-link">
          <a href="#">
            <i class='bx bx-book-alt'></i>
            <span class="link_name">Posts</span>
          </a>
          <i class='bx bxs-chevron-down arrow'></i>
        </div>
        <ul class="sub-menu">
          <li><a class="link_name" href="#">Posts</a></li>
          <li><a href="AllPost.php">Posts</a></li>
          <li><a href="AddPost.php">Add Posts</a></li>
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
            <span class="link_name">Users</span>
          </a>
          <i class="bx bxs-chevron-down arrow"></i>
        </div>
        <ul class="sub-menu">
          <li><a class="link_name" href="#">Users</a></li>
          <li><a href="manage.php">manage_users</a></li>
          <li><a href="register.php">register_users</a></li>
        </ul>
      </li>';
      }
      ?>


      <?php
      if ($_SESSION['role'] == 'SuperAdmin') {
        echo '
     <li>
        <a href="">
          <i class="bx bx-cog"></i>
          <span class="link_name">Website Settings</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="#">Settings</a></li>
        </ul>
      </li>';
      }
      ?>
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