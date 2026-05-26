// =============================================
//  FoodieHub - Restaurant Management System
//  Main JavaScript File
// =============================================


// add to cart function (uses session via AJAX-like form)
function addToCart(food_id, food_name, price) {

    // create a hidden form and submit it
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '../customer/cart.php';

    var fields = {
        add_to_cart: '1',
        food_id:     food_id,
        food_name:   food_name,
        price:       price
    };

    for(var key in fields){
        var input    = document.createElement('input');
        input.type   = 'hidden';
        input.name   = key;
        input.value  = fields[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);

    // show toast notification first then submit
    showToast(food_name + ' added to cart!');

    setTimeout(function(){
        form.submit();
    }, 800);
}


// show toast notification
function showToast(message) {
    var toastEl = document.getElementById('cartToast');
    var msgEl   = document.getElementById('toastMsg');

    if(toastEl && msgEl){
        msgEl.textContent = message;
        var toast = new bootstrap.Toast(toastEl, { delay: 2000 });
        toast.show();
    }
}


// confirm delete with nicer message
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}


// auto hide alerts after 4 seconds
document.addEventListener('DOMContentLoaded', function(){

    // auto hide alerts
    var alerts = document.querySelectorAll('.alert:not(.alert-info)');
    alerts.forEach(function(alert){
        setTimeout(function(){
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity    = '0';
            setTimeout(function(){
                alert.remove();
            }, 500);
        }, 4000);
    });

    // add active class to current nav link
    var currentPage = window.location.pathname.split('/').pop();
    var navLinks    = document.querySelectorAll('.navbar .nav-link');
    navLinks.forEach(function(link){
        var href = link.getAttribute('href');
        if(href && href.includes(currentPage) && currentPage !== ''){
            link.style.background = 'rgba(255,255,255,0.2)';
        }
    });

    // price input validation - only allow numbers
    var priceInputs = document.querySelectorAll('input[name="price"]');
    priceInputs.forEach(function(input){
        input.addEventListener('input', function(){
            if(this.value < 0) this.value = 0;
        });
    });

    // phone validation - only digits
    var phoneInputs = document.querySelectorAll('input[name="phone"]');
    phoneInputs.forEach(function(input){
        input.addEventListener('input', function(){
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    // quantity inputs - prevent 0 or negative
    var qtyInputs = document.querySelectorAll('input[name="quantity[]"], input[name="qty"]');
    qtyInputs.forEach(function(input){
        input.addEventListener('change', function(){
            if(parseInt(this.value) < 1) this.value = 1;
        });
    });

    // food card hover effects
    var foodCards = document.querySelectorAll('.food-card, .food-menu-card');
    foodCards.forEach(function(card){
        card.addEventListener('mouseenter', function(){
            this.style.boxShadow = '0 15px 40px rgba(232,129,58,0.25)';
        });
        card.addEventListener('mouseleave', function(){
            this.style.boxShadow = '';
        });
    });

    // smooth scroll to top button
    var scrollBtn = document.createElement('button');
    scrollBtn.innerHTML     = '<i class="bi bi-arrow-up"></i>';
    scrollBtn.style.cssText = 'position:fixed;bottom:80px;right:20px;width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#b5451b,#e8813a);color:white;border:none;display:none;cursor:pointer;z-index:999;box-shadow:0 3px 15px rgba(0,0,0,0.2);font-size:18px;transition:0.3s;';
    document.body.appendChild(scrollBtn);

    window.addEventListener('scroll', function(){
        if(window.scrollY > 300){
            scrollBtn.style.display = 'block';
        } else {
            scrollBtn.style.display = 'none';
        }
    });

    scrollBtn.addEventListener('click', function(){
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

});


// form validation before submit
function validateForm(formId) {
    var form   = document.getElementById(formId);
    var inputs = form.querySelectorAll('input[required], select[required]');
    var valid  = true;

    inputs.forEach(function(input){
        if(!input.value.trim()){
            input.style.borderColor = '#dc3545';
            valid = false;
        } else {
            input.style.borderColor = '#198754';
        }
    });

    return valid;
}


// preview image before upload
function previewImage(input) {
    if(input.files && input.files[0]){
        var reader = new FileReader();
        reader.onload = function(e){
            var img = document.getElementById('imgPreview');
            if(img){
                img.src          = e.target.result;
                img.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}


// toggle password visibility
function togglePassword() {
    var field = document.getElementById('passwordField');
    var icon  = document.getElementById('eyeIcon');
    if(field){
        if(field.type === 'password'){
            field.type = 'text';
            if(icon) icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            field.type = 'password';
            if(icon) icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
}
