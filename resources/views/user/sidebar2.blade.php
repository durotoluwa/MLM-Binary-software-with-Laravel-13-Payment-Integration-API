   <div class="dlabnav">
  <div class="dlabnav-scroll">
    <div class="dropdown header-profile2 ">
      <a class="nav-link " href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
        <div class="header-info2 d-flex align-items-center">
          <img src="images/profile/pic1.jpg" alt="">
          <div class="d-flex align-items-center sidebar-info">
            <div>
              <span class="font-w400 d-block">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
</span>
              <small class="text-end font-w400">Membership</small>
            </div>
            <i class="fas fa-chevron-down"></i>
          </div>
        </div>
      </a>
      <div class="dropdown-menu dropdown-menu-end">
        <a href="app-profile" class="dropdown-item ai-icon ">
          <svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <span class="ms-2">Profile </span>
        </a>
       
     <form method="POST" action="{{ route('logout') }}" id="logout-form">
    @csrf
    <a href="#" class="dropdown-item ai-icon"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <svg xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        <span class="ms-2">Logout</span>
    </a>
</form>

      </div>
    </div>
    <ul class="metismenu" id="menu">
   
  
  
    </ul>
 
    <div class="copyright">
      <p>
        <strong>DLT Health Plus</strong> © <span class="current-year">2025</span> All Rights Reserved
      </p>
       
    </div>
  </div>
</div>