   <div class="dlabnav">
  <div class="dlabnav-scroll">
    <div class="dropdown header-profile2 ">
      <a class="nav-link " href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
        <div class="header-info2 d-flex align-items-center">
         <img src="{{ Auth::user()->profile_image }}" width="20" alt="User">
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
        <a href="{{ route('admin.dashboard') }}" class="" aria-expanded="false">
        <i class="fa-solid fa-gauge"></i>
          <span class="nav-text">Dashboard</span>
        </a>
      </li>

 
      <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
       <i class="fa-solid fa-gear"></i>
          <span class="nav-text">Control Settings</span>
        </a>
        <ul aria-expanded="false">
          <li>
            <a href="job-list">Job Lists</a>
          </li>
          <li>
            <a href="job-view">Job View</a>
          </li>
          <li>
            <a href="job-application">Job Application</a>
          </li>
          <li>
            <a href="apply-job">Apply Job</a>
          </li>
          <li>
            <a href="new-job">New Job</a>
          </li>
          <li>
            <a href="user-profile">User Profile</a>
          </li>
        </ul>
      </li>
      <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
          <i class="flaticon-381-user-7"></i>
          <span class="nav-text">Profile</span>
        </a>
        <ul aria-expanded="false">
          <li>
            <a href="profile-overview">Overview</a>
          </li>
          <li>
            <a href="profile-projects">Projects</a>
          </li>
          <li>
            <a href="profile-projects-details">Projects Details</a>
          </li>
          <li>
            <a href="profile-campaigns">Campaigns</a>
          </li>
          <li>
            <a href="profile-documents">Documents</a>
          </li>
          <li>
            <a href="profile-followers">Followers</a>
          </li>
          <li>
            <a href="profile-activity">Activity</a>
          </li>
        </ul>
      </li>
      <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
          <i class="flaticon-381-user-4"></i>
          <span class="nav-text">Account</span>
        </a>
        <ul aria-expanded="false">
          <li>
            <a href="account-overview">Overview</a>
          </li>
          <li>
            <a href="account-settings">Settings</a>
          </li>
          <li>
            <a href="account-security">Security</a>
          </li>
          <li>
            <a href="account-activity">Activity</a>
          </li>
          <li>
            <a href="account-billing">Billing</a>
          </li>
          <li>
            <a href="account-statements">Statements</a>
          </li>
          <li>
            <a href="account-referrals">Referrals</a>
          </li>
          <li>
            <a href="account-api-keys">Api Keys</a>
          </li>
          <li>
            <a href="account-logs">Logs</a>
          </li>
        </ul>
      </li>
      <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
          <i class="flaticon-381-internet"></i>
          <span class="nav-text">AIKit</span>
        </a>
        <ul aria-expanded="false">
          <li>
            <a href="ai-auto-write">Auto Writer</a>
          </li>
          <li>
            <a href="ai-scheduled">Scheduler</a>
          </li>
          <li>
            <a href="ai-repurpose">Repurpose</a>
          </li>
          <li>
            <a href="ai-rss">RSS</a>
          </li>
          <li>
            <a href="ai-chatbot">Chatbot</a>
          </li>
          <li>
            <a href="ai-fine-tune-models">Fine-tune Models</a>
          </li>
          <li>
            <a href="ai-prompt">AI Menu Prompts</a>
          </li>
          <li>
            <a href="ai-setting">Settings</a>
          </li>
          <li>
            <a href="ai-import">Export/Import Settings</a>
          </li>
        </ul>
      </li>
      <li>
        <a class="has-arrow " href="javascript:void(0);" aria-expanded="false">
          <i class="fa-solid fa-gear"></i>
          <span class="nav-text">CMS</span>
        </a>
        <ul aria-expanded="false">
          <li>
            <a href="content">Content</a>
          </li>
          <li>
            <a href="content-add">Add Content</a>
          </li>
          <li>
            <a href="menu">Menus</a>
          </li>
          <li>
            <a href="email-template">Email Template</a>
          </li>
          <li>
            <a href="add-email">Add Email</a>
          </li>
          <li>
            <a href="blog">Blog</a>
          </li>
          <li>
            <a href="add-blog">Add Blog</a>
          </li>
          <li>
            <a href="blog-category">Blog Category</a>
          </li>
        </ul>
      </li>
    
      
  
  
    </ul>
 
    <div class="copyright">
      <p>
        <strong>DLT Health Plus</strong> © <span class="current-year">2025</span> All Rights Reserved
      </p>
       
    </div>
  </div>
</div>