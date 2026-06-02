<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="csrf-token" content="{{ csrf_token() }}">



     <meta http-equiv="X-UA-Compatible" content="IE=edge">
 
    <meta name="robots" content="" >
    <meta name="keywords" content="Divine Leverage Team Limited, DLTL, MLM, Health Plus, Nigeria, Africa">
    <meta name="description" content="Divine Leverage Team Limited, the fastest growing Multilevel Marketing (MLM) company in Africa.">
    <meta property="og:title" content="DLTL :  Admin Dashboard Divine Leverage Team Limited">
    <meta property="og:description" content="Divine Leverage Team Limited, the fastest growing Multilevel Marketing (MLM) company in Africa." >
    <meta property="og:image" content="https://www.dlthealthplus.com/image/slider/1.jpg">
    <meta name="format-detection" content="telephone=no">

    <meta name="twitter:title" content="DLTL :  Admin Dashboard Divine Leverage Team Limited">
    <meta name="twitter:description" content="Divine Leverage Team Limited, the fastest growing Multilevel Marketing (MLM) company in Africa.">
    <meta name="twitter:image" content="https://www.dlthealthplus.com/image/slider/1.jpg">
    <meta name="twitter:card" content="summary_large_image">
 
    <!-- PAGE TITLE HERE -->
    <title>DLTL : Divine Leverage Team Limited Dashboard</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

 
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/fontawesome.min.css" integrity="sha512-v8QQ0YQ3H4K6Ic3PJkym91KoeNT5S3PnDKvqnwqFD1oiqIl653crGZplPdU5KKtHjO0QKcQ2aUlQZYjHczkmGw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/regular.min.css" integrity="sha512-8hM9a+2hrLBhOuB3uiy+QIXBsu6Qk+snsP1CboFQW6pdt/yYz0IcDp/+CGv5m39r9doGUc/zw6aBpyLF6XFgzg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/solid.min.css" integrity="sha512-DzC7h7+bDlpXPDQsX/0fShhf1dLxXlHuhPBkBo/5wJWRoTU6YL7moeiNoej6q3wh5ti78C57Tu1JwTNlcgHSjg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

                <!-- Styles -->
    
        <link href="{{ asset('assets/css/bootstrap-select.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/bootstrap-datepicker.min.css') }}" rel="stylesheet">

	<!-- Datatable -->
<link href="{{ asset('assets/css/jquery.dataTables.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">



        <link href="{{ asset('assets/css/smart_wizard.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/metisMenu.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">

<!----=========================  CDN Links ========================= --->
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right", // or toast-bottom-right, etc.
    };
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/treant-js/1.0/Treant.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/treant-js/1.0/Treant.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>



    </head>
    <body class="font-sans antialiased">
	<!--****  Preloader start *******-->
	<div id="preloader">
		<div class="lds-ripple">
			<div></div>
			<div></div>
		</div>
    </div>
	<!--***** Preloader end *******-->
<div id="main-wrapper">
        <div class="min-h-screen bg-gray-100">
       
            <!-- Page Content -->
            <main>
             @yield('content')
            </main>
        </div></div>




 
        <script src="{{ asset('assets/js/global.min.js') }}"></script>
         <script src="{{ asset('assets/js/bootstrap-select.min.js') }}"></script>
        <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
        <script src="{{ asset('assets/js/jquery.steps.min.js') }}"></script>

        <script src="{{ asset('assets/js/apexchart.js') }}"></script>
        <script src="{{ asset('assets/js/chart.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/js/jquery.peity.min.js') }}"></script>
   

        <script src="{{ asset('assets/js/dashboard-1.js') }}"></script>
        <script src="{{ asset('assets/js/owl.carousel.js') }}"></script>
<!-- Datatable -->
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/responsive.js') }}"></script>
<script src="{{ asset('assets/js/datatables.init.js ') }}"></script>

        <script src="{{ asset('assets/js/custom.min.js') }}"></script>
        <script src="{{ asset('assets/js/dlabnav-init.js') }}"></script>
        <script src="{{ asset('assets/js/demo.js') }}"></script>


<script>
	function JobickCarousel()
	{
		/*  testimonial one function by = owl.carousel.js */
		jQuery('.front-view-slider').owlCarousel({
			loop:false,
			margin:30,
			nav:false,
			autoplaySpeed: 3000,
			navSpeed: 3000,
			autoWidth:true,
			paginationSpeed: 3000,
			slideSpeed: 3000,
			smartSpeed: 3000,
			autoplay: false,
			animateOut: 'fadeOut',
			dots:false,
			navText: ['', ''],
			responsive:{
				0:{
					items:1,
					
					margin:10
				},
				
				480:{
					items:1
				},			
				
				767:{
					items:3
				},
				1750:{
					items:3
				}
			}
		})
	}
	jQuery(window).on('load',function(){
		setTimeout(function(){
			JobickCarousel();
		}, 1000);
	});
</script>
	<!-- Plugins JS Ends-->








<script>
document.addEventListener('DOMContentLoaded', function () {
  const uplineInput = document.getElementById('upline_username');
  const uplineInfo = document.getElementById('upline_info');
  const uplineError = document.getElementById('upline_error');

  const sponsorInput = document.getElementById('sponsor_username');
  const sponsorError = document.getElementById('sponsor_error');

  uplineInput.addEventListener('input', function () {
    const username = uplineInput.value.trim();

    // Reset messages
    uplineInfo.textContent = '';
    uplineError.textContent = '';
    sponsorError.textContent = '';
    sponsorInput.value = '';

    if (username === '') {
      sponsorInput.required = false;
      sponsorError.textContent = '';
      return;
    }

    fetch('/check-upline', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({ username: username }),
    })
      .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
      })
      .then(data => {
        if (!data.exists) {
          uplineError.textContent = 'Upline username does not exist.';
          sponsorInput.required = false;
          return;
        }

        uplineInfo.innerHTML = `<span style="color: green;">Upline found: ${data.user.first_name} ${data.user.last_name}</span>`;

        if (data.leftOccupied && data.rightOccupied) {
          sponsorInput.required = true;
          sponsorError.textContent = 'Sponsor username is required because the upline user 2 legs are fully occupied.';
        } else {
          sponsorInput.required = false;
          sponsorError.textContent = '';
        }
      })
      .catch(error => {
        uplineError.textContent = 'Error checking upline username.';
        sponsorInput.required = false;
        sponsorError.textContent = '';
        console.error('Error checking upline username:', error);
      });
  });

  // Trigger check if upline already has a value on load
  if (uplineInput.value.trim() !== '') {
    const event = new Event('input');
    uplineInput.dispatchEvent(event);
  }
});
</script>






<script>
document.addEventListener('DOMContentLoaded', function () {
    var muteUserModal = document.getElementById('muteUserModal');
    muteUserModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var userId = button.getAttribute('data-user-id');
        var userName = button.getAttribute('data-user-name');
        var isMuted = button.getAttribute('data-is-muted');

        document.getElementById('muteUserName').textContent = userName;

        let form = document.getElementById('muteUserForm');
        form.action = `/superadmin/member/${userId}/toggle-mute`;

        // Set current value
        document.getElementById('muteSelect').value = isMuted;
    });
});
</script>



 
<script>
document.querySelector('select[name="bank_code"]').addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('bank_name').value = selectedOption.getAttribute('data-name');
});
</script>


 


 

<script>
$(document).ready(function() {
    // Initialize Select2 for user search dropdown
    $('#user_id').select2({
        placeholder: '-- Choose User --',
        allowClear: true,
        width: '100%'
    });

    // When a user is selected
    $('#user_id').on('change', function() {
        const userId = $(this).val();
        const infoBox = $('#last-package-info');
        const packageSelect = $('#package_id');

        // Reset UI if no user selected
        if (!userId) {
            infoBox.addClass('d-none').html('');
            packageSelect.html('<option value="">-- Select Package --</option>');
            return;
        }

        $.ajax({
        url: "{{ url('/superadmin/user') }}/" + userId + "/package-info",

            type: 'GET',
            dataType: 'json',
            beforeSend: function() {
                infoBox
                    .removeClass('d-none alert-success alert-danger')
                    .addClass('alert alert-info')
                    .html('<i class="fa fa-spinner fa-spin"></i> Loading package details...');
                packageSelect.html('<option>Loading...</option>');
            },
            success: function(res) {
                if (res.status === 'success') {

                    // Show last package info
                    if (res.last_package) {
                        const pkg = res.last_package;
                        infoBox
                            .removeClass('alert-info alert-danger')
                            .addClass('alert alert-success')
                            .html(`
                                <strong>Last Package:</strong> ${pkg.name ?? 'N/A'}<br>
                                <strong>Price:</strong> ₦${pkg.price ?? '0'}<br>
                                <strong>Status:</strong> ${pkg.status ?? 'N/A'}<br>
                                <strong>Date:</strong> ${pkg.date ?? 'N/A'}
                            `);
                    } else {
                        infoBox
                            .removeClass('alert-info alert-danger')
                            .addClass('alert alert-success')
                            .html('This user has no previous package.');
                    }

                    // Populate the package dropdown
                    packageSelect.html('<option value="">-- Select Package --</option>');
                    if (res.available_packages && res.available_packages.length > 0) {
                        res.available_packages.forEach(pkg => {
                            packageSelect.append(`
                                <option value="${pkg.id}">
                                    ${pkg.packageName} (₦${pkg.price})
                                </option>
                            `);
                        });
                    } else {
                        packageSelect.html('<option value="">No higher package available</option>');
                    }

                } else {
                    infoBox
                        .removeClass('alert-info alert-success')
                        .addClass('alert alert-danger')
                        .html(res.message || 'Error loading data');
                    packageSelect.html('<option value="">-- Select Package --</option>');
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                infoBox
                    .removeClass('alert-info alert-success')
                    .addClass('alert alert-danger')
                    .html('Network error fetching package info.');
                packageSelect.html('<option value="">-- Select Package --</option>');
            }
        });
    });
});
</script>


        <!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- jQuery (required by Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    </body>
</html>
