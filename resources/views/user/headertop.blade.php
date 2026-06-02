     <div class="header">
  <div class="header-content">
    <nav class="navbar navbar-expand">
      <div class="collapse navbar-collapse justify-content-between">
        <div class="header-left">
          <div class="dashboard_bar">
            
                         @php
                        $hour = now()->format('H'); // Get current hour in 24-hour format
                    @endphp
                    
                    @if ($hour >= 5 && $hour < 12)
                        Good Morning, {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}

                    @elseif ($hour >= 12 && $hour < 18)
                        Good Afternoon, {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}

                    @else
                        Good Evening, {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}

                    @endif
            
             </div>
         
        </div>
        <ul class="navbar-nav header-right">
          <li class="nav-item dropdown notification_dropdown">
            <a class="nav-link bell dlab-theme-mode p-0" href="javascript:void(0);">
              <i id="icon-light" class="fas fa-sun"></i>
              <i id="icon-dark" class="fas fa-moon"></i>
            </a>
          </li>

          <!------notofication--
          <li class="nav-item dropdown notification_dropdown">
            <a class="nav-link bell-link " href="javascript:void(0);">
            <i class="fa-solid fa-cart-shopping"></i>
              <span class="badge light text-white bg-primary rounded-circle">0</span>
            </a>
          </li>----->


 <!------notofication Items---->
@php
    $cart = session('cart', []);
    $cartCount = count($cart);
$cartTotal = collect($cart)->sum(function ($item) {
    $priceWithApc = $item['price'] + ($item['apc'] ?? 0);
    return $priceWithApc * $item['quantity'];
});
@endphp

<li class="nav-item dropdown notification_dropdown">
    <a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
        <i class="fa-solid fa-cart-shopping"></i>
        @if($cartCount > 0)
            <span class="badge light text-white bg-primary rounded-circle">{{ $cartCount }}</span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-end">
        <div class="d-flex justify-content-between align-items-center px-3 py-2">
            <strong>Cart</strong>
            @if($cartCount > 0)
                <button class="btn btn-sm btn-danger btn-clear-cart" title="Clear All">
                    <i class="fa fa-trash"></i> Clear All
                </button>
            @endif
        </div>

        <div class="widget-media dlab-scroll p-3" style="max-height:380px; overflow-y:auto;" id="cart-dropdown-body">
            <ul class="timeline mb-0">
                @forelse($cart as $id => $item)
                    <li class="cart-item" data-id="{{ $id }}">
                        <div class="timeline-panel d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <img alt="image" width="30" src="{{ asset($item['image'] ?? 'default.png') }}">
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $item['name'] }}</h6>
                                    <small class="d-block">
    ₦{{ number_format($item['price'], 2) }} + ₦{{ number_format($item['apc'] ?? 0, 2) }} x {{ $item['quantity'] }}
</small>

                                </div>
                            </div>
                            <button class="btn btn-sm btn-danger btn-remove-cart" data-id="{{ $id }}" title="Remove Item">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </li>
                @empty
                    <li>
                        <div class="timeline-panel text-center text-muted">
                            Cart is empty
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>

        @if($cartCount > 0)
            <div class="px-3 py-2 border-top">
                <div class="d-flex justify-content-between">
                    <strong>Total:</strong>
                    <span>₦{{ number_format($cartTotal, 2) }}</span>
                </div>
            </div>

            <a class="all-notification d-block text-center" href="{{ route('checkout') }}">
                Proceed to Checkout <i class="ti-arrow-end"></i>
            </a>
        @endif
    </div>
</li>





          <li class="nav-item dropdown notification_dropdown">
            <a class="nav-link " href="javascript:void(0);" data-bs-toggle="dropdown">
              <svg xmlns="http://www.w3.org/2000/svg" width="23.262" height="24" viewBox="0 0 23.262 24">
                <g id="icon" transform="translate(-1565 90)">
                  <path id="setting_1_" data-name="setting (1)" d="M30.45,13.908l-1-.822a1.406,1.406,0,0,1,0-2.171l1-.822a1.869,1.869,0,0,0,.432-2.385L28.911,4.293a1.869,1.869,0,0,0-2.282-.818l-1.211.454a1.406,1.406,0,0,1-1.88-1.086l-.213-1.276A1.869,1.869,0,0,0,21.475,0H17.533a1.869,1.869,0,0,0-1.849,1.567L15.47,2.842a1.406,1.406,0,0,1-1.88,1.086l-1.211-.454a1.869,1.869,0,0,0-2.282.818L8.126,7.707a1.869,1.869,0,0,0,.432,2.385l1,.822a1.406,1.406,0,0,1,0,2.171l-1,.822a1.869,1.869,0,0,0-.432,2.385L10.1,19.707a1.869,1.869,0,0,0,2.282.818l1.211-.454a1.406,1.406,0,0,1,1.88,1.086l.213,1.276A1.869,1.869,0,0,0,17.533,24h3.943a1.869,1.869,0,0,0,1.849-1.567l.213-1.276a1.406,1.406,0,0,1,1.88-1.086l1.211.454a1.869,1.869,0,0,0,2.282-.818l1.972-3.415a1.869,1.869,0,0,0-.432-2.385ZM27.287,18.77l-1.211-.454a3.281,3.281,0,0,0-4.388,2.533l-.213,1.276H17.533l-.213-1.276a3.281,3.281,0,0,0-4.388-2.533l-1.211.454L9.75,15.355l1-.822a3.281,3.281,0,0,0,0-5.067l-1-.822L11.721,5.23l1.211.454A3.281,3.281,0,0,0,17.32,3.151l.213-1.276h3.943l.213,1.276a3.281,3.281,0,0,0,4.388,2.533l1.211-.454,1.972,3.414h0l-1,.822a3.281,3.281,0,0,0,0,5.067l1,.822ZM19.5,7.375A4.625,4.625,0,1,0,24.129,12,4.63,4.63,0,0,0,19.5,7.375Zm0,7.375A2.75,2.75,0,1,1,22.254,12,2.753,2.753,0,0,1,19.5,14.75Z" transform="translate(1557.127 -90)" />
                </g>
              </svg>
              <span class="badge light text-white bg-primary rounded-circle">0</span>
            </a>


            <!----------
            <div class="dropdown-menu dropdown-menu-end">
              <div id="DZ_W_TimeLine02" class="widget-timeline dlab-scroll style-1 p-3 height370">
                <ul class="timeline">
                  <li>
                    <div class="timeline-badge primary"></div>
                    <a class="timeline-panel text-muted" href="javascript:void(0);">
                      <span>10 minutes ago</span>
                      <h6 class="mb-0">Youtube, a video-sharing website, goes live <strong class="text-primary">$500</strong>. </h6>
                    </a>
                  </li>
                  <li>
                    <div class="timeline-badge info"></div>
                    <a class="timeline-panel text-muted" href="javascript:void(0);">
                      <span>20 minutes ago</span>
                      <h6 class="mb-0">New order placed <strong class="text-info">#XF-2356.</strong>
                      </h6>
                      <p class="mb-0">Quisque a consequat ante Sit amet magna at volutapt...</p>
                    </a>
                  </li>
                  <li>
                    <div class="timeline-badge danger"></div>
                    <a class="timeline-panel text-muted" href="javascript:void(0);">
                      <span>30 minutes ago</span>
                      <h6 class="mb-0">john just buy your product <strong class="text-warning">Sell $250</strong>
                      </h6>
                    </a>
                  </li>
                  <li>
                    <div class="timeline-badge success"></div>
                    <a class="timeline-panel text-muted" href="javascript:void(0);">
                      <span>15 minutes ago</span>
                      <h6 class="mb-0">StumbleUpon is acquired by eBay. </h6>
                    </a>
                  </li>
                  <li>
                    <div class="timeline-badge warning"></div>
                    <a class="timeline-panel text-muted" href="javascript:void(0);">
                      <span>20 minutes ago</span>
                      <h6 class="mb-0">Mashable, a news website and blog, goes live.</h6>
                    </a>
                  </li>
                  <li>
                    <div class="timeline-badge dark"></div>
                    <a class="timeline-panel text-muted" href="javascript:void(0);">
                      <span>20 minutes ago</span>
                      <h6 class="mb-0">Mashable, a news website and blog, goes live.</h6>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </li> ------>

          <li class="nav-item dropdown header-profile">
            <a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
          <img src="{{ !empty(Auth::user()->profile_photo_path) ? Auth::user()->profile_photo_path : asset('default/avatar.png') }}" width="20" >

            </a>
            <div class="dropdown-menu dropdown-menu-end">
              <a href="app-profile" class="dropdown-item ai-icon">
                <svg id="icon-user2" xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
          </li>
        </ul>
      </div>
    </nav>
  </div>
</div>

