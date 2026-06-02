   <div class="dlabnav">
  <div class="dlabnav-scroll">
    <div class="dropdown header-profile2 ">
      <a class="nav-link " href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
        <div class="header-info2 d-flex align-items-center">
                 <img src="{{ !empty(Auth::user()->profile_photo_path) ? Auth::user()->profile_photo_path : asset('default/avatar.png') }}" width="20" >
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
      <li>
        <a href="{{ route('user.dashboard') }}" class="" aria-expanded="false">
        <i class="fa-solid fa-gauge"></i>
          <span class="nav-text">Dashboard</span>
        </a>
      </li>

  <li>
        <a href="{{ route('user.package') }}" class="" aria-expanded="false">
     <i class="fa-solid fa-bottle-droplet"></i>
          <span class="nav-text">Package</span>
        </a>
      </li>


        <li>
        <a href="{{ route('user.order_product') }}" class="" aria-expanded="false">
    <i class="fa-solid fa-bottle-water"></i>
          <span class="nav-text">Product Order</span>
        </a>
      </li>


      
        <li>
        <a href="{{ route('user.topup_wallet') }}" class="" aria-expanded="false">
    <i class="fa-solid fa-wallet"></i>
          <span class="nav-text">Top-up Wallet</span>
        </a>
      </li>



      
        <li>
        <a href="{{ route('user.withdrawal_page') }}" class="" aria-expanded="false">
 <i class="fa-solid fa-money-bills"></i>
          <span class="nav-text">Withdraw</span>
        </a>
      </li>
 
      <!----
 <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
   <i class="fa-solid fa-credit-card"></i>
          <span class="nav-text">Bonuses</span>
        </a>
        <ul aria-expanded="false">
           <li>
            <a href="">Registration Bonus</a>
          </li>
             <li>
            <a href="">Matching Bonus</a>
          </li>
          <li>
            <a href="">Referal Bonus</a>
          </li>
          <li>
            <a href="">Order History</a>
          </li>
        
        </ul>
      </li>

 <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
   <i class="fa-solid fa-credit-card"></i>
          <span class="nav-text">Transactions</span>
        </a>
        <ul aria-expanded="false">.
           <li>
            <a href="">Wallet History</a>
          </li>
             <li>
            <a href="">Withdrawal History</a>
          </li>
          <li>
            <a href="">Package History</a>
          </li>
          <li>
            <a href="">Order History</a>
          </li>
        
        </ul>
      </li>

--->

  <li>
        <a href="{{ route('user.myprofile') }}" class="" aria-expanded="false">
      <i class="fa-solid fa-users-gear"></i>
          <span class="nav-text">My Profile</span>
        </a>
      </li>




     
      
  
  
    </ul>
 
    <div class="copyright">
      <p>
        <strong>DLT Health Plus</strong> © <span class="current-year">2026</span> All Rights Reserved
      </p>
       
    </div>
  </div>
</div>