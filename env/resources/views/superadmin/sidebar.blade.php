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
              <small class="text-end font-w400">Superadmin</small>
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
        <a href="{{ route('superadmin.dashboard') }}" class="" aria-expanded="false">
        <i class="fa-solid fa-gauge"></i>
          <span class="nav-text">Dashboard</span>
        </a>
      </li>

 
      <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
       <i class="fa-solid fa-gear"></i>
          <span class="nav-text">Administration</span>
        </a>
        <ul aria-expanded="false">
          <li>
            <a href="{{ route('permissions.index') }}">Permissions</a>
          </li>
          <li>
            <a href="{{ route('role_permissions.index') }}">Assign Permission</a>
          </li>
          <li>
            <a href="{{ route('user_permissions.index') }}">User Permission</a>
          </li>
          <li>
            <a href="{{ route('superadmin.settings.edit') }}">Reg. Bonus Settings</a>
          </li>

             <li>
            <a href="{{ route('superadmin.settings.edit') }}">Admin Settings</a>
          </li>

             <li>
            <a href="{{ route('incentive_settings.index') }}">Incentive Settings</a>
          </li>


            <li>
            <a href="{{ route('superadmin.migration.migrate_user') }}">User Migration</a>
          </li>
    
        </ul>
      </li>


 <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
         <i class="fa-solid fa-users"></i>
          <span class="nav-text">Members</span>
        </a>
        <ul aria-expanded="false">

<li>
    <a href="{{ route('superadmin.registermember.step', 1) }}">Add Members</a>
</li>


          <li>
            <a href="{{ route('superadmin.member.allMembers') }}">All Members</a>
          </li>
          <li>
            <a href="{{ route('superadmin.member.activeMembers') }}">Active Members</a>
          </li>

         

               <li>
            <a href="{{ route('superadmin.member.inactiveMembers') }}">Inactive Members</a>
          </li>
               <li>
            <a href="{{ route('superadmin.member.pendingMembers') }}">Pending Members</a>
          </li>
          
          <li>
            <a href="{{ route('superadmin.member.mutedMembers') }}">Muted Members</a>
          </li>
<!--=====
           <li>
            <a href="">Create New Member</a>
          </li> ======-->
         
        </ul>
      </li>


 <li>
        <a href="{{ route('superadmin.incentive_settings.incentive_list') }}" class="" aria-expanded="false">
     <i class="fa-solid fa-ranking-star"></i>
          <span class="nav-text">Incentive</span>
        </a>
      </li>



  <li>
        <a href="{{ route('superadmin.package.buy_package') }}" class="" aria-expanded="false">
      <i class="fa-solid fa-basket-shopping"></i>
          <span class="nav-text">Buy Package</span>
        </a>
      </li>

 
        <li>
        <a href="{{ route('superadmin.package.order_product') }}" class="" aria-expanded="false">
        <i class="fa-solid fa-list-check"></i>
          <span class="nav-text">Place Order</span>
        </a>
      </li>


      <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
        <i class="fa-solid fa-credit-card"></i>
          <span class="nav-text">Transactions</span>
        </a>
        <ul aria-expanded="false">
          <li>
            <a href="{{ route('superadmin.transaction.pendingRegistrationPayment')}}">Pending Reg. Payment</a>
          </li>

          
          <li>
            <a href="{{ route('superadmin.transaction.registrationPayment')}}">Approved Reg. Payment</a>
          </li>

 <li>
            <a href="{{ route('superadmin.transaction.pendingwallettopup')}}">Pending Wallet Topup</a>
          </li>


           <li>
            <a href="{{ route('superadmin.transaction.payout_history')}}">Payout History</a>
          </li>


        
        </ul>
      </li>


  <li>
       <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
        <i class="fa-solid fa-credit-card"></i>
          <span class="nav-text">History</span>
        </a>
        <ul aria-expanded="false">
          <li>
            <a href="{{ route('superadmin.history.wallet')}}">Wallet Deposit</a>
          </li>

          
          <li>
            <a href="{{ route('superadmin.history.registration')}}">Registration Payment</a>
          </li>

 <li>
            <a href="{{ route('superadmin.transaction.payout_history')}}">Payout History</a>
          </li>


        


        
        </ul>
      </li>




      <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
       <i class="fa-solid fa-coins"></i>
          <span class="nav-text">Bonuses</span>
        </a>
        <ul aria-expanded="false">
          <li>
            <a href="{{ route('superadmin.bonuses.regBonus') }}">Registration Bonus</a>
          </li>
         <li>
            <a href="{{ route('superadmin.bonuses.macthingBonus') }}">Macthing Bonus</a>
          </li>
          
       
        </ul>
      </li>
     
      <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
    <i class="fa-solid fa-bag-shopping"></i>
          <span class="nav-text">Package</span>
        </a>
        <ul aria-expanded="false">
          <li>
            <a href="{{ route('superadmin.package.package_list') }}">Package List</a>
          </li>

          
          <li>
            <a href="{{ route('superadmin.package.create_package') }}">Add Package</a>
          </li>

          
      
        </ul>
      </li>


            <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
    <i class="fa-solid fa-bag-shopping"></i>
          <span class="nav-text">Package Order</span>
        </a>
        <ul aria-expanded="false">
          <li>
            <a href="{{ route('superadmin.package.pendingpackageOrder') }}">Pending Package Order</a>
          </li>

          
          <li>
            <a href="{{ route('superadmin.package.approvepackageOrder') }}">Approved Package Order</a>
          </li>
      
        </ul>
      </li>


         <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
         <i class="fa-solid fa-cart-shopping"></i>
          <span class="nav-text">Product</span>
        </a>
        <ul aria-expanded="false">
          <li>
            <a href="{{ route('superadmin.product.product_list') }}">Product List</a>
          </li>
          
          <li>
            <a href="{{ route('superadmin.product.create_product') }}">Add Product</a>
          </li>
      
        </ul>
      </li>



      
         <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
         <i class="fa-solid fa-cart-shopping"></i>
          <span class="nav-text">Product Order</span>
        </a>
        <ul aria-expanded="false">
          <li>
            <a href="{{ route('superadmin.product.pendingproductOrder') }}">Pending Product Order</a>
          </li>
          
          <li>
            <a href="{{ route('superadmin.product.create_product') }}">Approved Product Order</a>
          </li>
      
        </ul>
      </li>
    
      

 
    <div class="copyright">
      <p>
        <strong>DLT Health Plus</strong> © <span class="current-year">2025</span> All Rights Reserved
      </p>
       
    </div>
  </div>
</div>