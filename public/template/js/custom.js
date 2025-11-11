
$(window).scroll(function () {
    if ($(window).scrollTop() >= 40) {
        $('.top-header').addClass('fixedheader');
    }
    else {
        $('.top-header').removeClass('fixedheader');
    }
});




$(".slidetoggle").click(function(){
    $(".innerbody").toggleClass("menu-collapse");
    });
    $(".closemenu-btn").click(function(){
    $(".innerbody").removeClass("menu-collapse");
});


$('.menubar > li > a').click(function() {
    $(".menubar > li > a.active").removeClass('active');
    $(this).toggleClass('active');
});

$(document).ready(function () {
    $('.js-example-basic-multiple').select2();
});





let list = document.querySelector('.slider .list');
let items = document.querySelectorAll('.slider .list .item');
let dots = document.querySelectorAll('.slider .dots li');
let prev = document.getElementById('prev');
let next = document.getElementById('next');

let active = 0;
let lengthitems = items.length - 1;

next.onclick = function() {
    if (active + 1 > lengthitems) {
        active = 0;
    } else {
        active = active + 1;
    }
    reloadslider();
}

prev.onclick = function() {
    if (active - 1 < 0) {
        active = lengthitems;
    } else {
        active = active - 1;
    }
    reloadslider();
}

let autoslide = setInterval(() => { next.click(); }, 3000);

function reloadslider() {
    let checkleft = items[active].offsetLeft;
    list.style.left = -checkleft + 'px';

    let lastactiveDot = document.querySelector('.slider .dots li.active');
    if (lastactiveDot) lastactiveDot.classList.remove('active');
    dots[active].classList.add('active');
}

dots.forEach((li, key) => {
    li.addEventListener('click', function() {
        active = key;
        reloadslider();
    })
})




  

      

      

    
      
