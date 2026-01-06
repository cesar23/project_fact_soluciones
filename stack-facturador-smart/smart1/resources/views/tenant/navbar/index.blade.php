<?php
use App\Models\Tenant\QuickAccess;
$quickaccess=QuickAccess::all();
?>
<style>
    .quick-access-navbar {
        background: transparent;
        border-radius: 0;
        padding: 5px;
        margin:10px 0;
        box-shadow: none;
        backdrop-filter: none;
        border: none;
    }

    .quick-access-title {
        display: block;
        color: #238dcf;
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 10px;
        text-align: center;
    }

    .quick-access-menu {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-start;
        align-items: flex-start;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .quick-access-item {
        flex: 0 0 auto;
    }

    .quick-access-link {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        padding: 10px 14px;
        background: #ffffff;
        color: #238dcf;
        text-decoration: none;
        border-radius: 10px;
        transition: all 0.3s ease;
        border: 2px solid #238dcf;
        font-size: 0.9rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .quick-access-link:hover {
        background: #238dcf;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(35, 141, 207, 0.35);
        color: #ffffff;
        text-decoration: none;
        border-color: #238dcf;
    }

    .quick-access-link:active {
        transform: translateY(0);
    }

    .quick-access-link i {
        font-size: 1.1rem;
    }

    .hamburger-container {
        display: none;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        padding: 8px;
        border-radius: 8px;
        background: #ffffff;
        border: 2px solid #238dcf;
        transition: all 0.3s ease;
    }

    .hamburger-container:hover {
        background: #238dcf;
        border-color: #238dcf;
        color: #ffffff;
    }

    .hamburger-container:hover .hamburger .bar {
        background-color: #ffffff;
    }

    .hamburger {
        display: flex;
        flex-direction: column;
        gap: 3px;
        width: 20px;
        height: 16px;
        cursor: pointer;
    }

    .hamburger .bar {
        width: 100%;
        height: 2px;
        background-color: #238dcf;
        border-radius: 1px;
        transition: all 0.3s ease;
    }

    .hamburger.active .bar:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
    }

    .hamburger.active .bar:nth-child(2) {
        opacity: 0;
    }

    .hamburger.active .bar:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -6px);
    }

    .hamburger-text {
        color: #238dcf;
        font-weight: 500;
        font-size: 0.9rem;
        text-shadow: none;
    }

    .hamburger-container:hover .hamburger-text {
        color: #ffffff;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .quick-access-navbar {
            padding: 10px;
            margin: 10px 0;
        }

        .hamburger-container {
            display: flex !important;
        }

        .quick-access-menu {
            display: flex !important;
            gap: 8px;
        }

        .quick-access-link {
            width: 40px;
            height: 40px;
            padding: 0;
            justify-content: center;
        }

        .quick-access-link span {
            display: none;
        }
    }

    @media (max-width: 480px) {
        .quick-access-navbar {
            padding: 8px;
            border-radius: 10px;
        }

        .quick-access-link {
            width: 38px;
            height: 38px;
        }
        
        .quick-access-link i {
            font-size: 1rem;
        }
    }
</style>

@if($quickaccess->count() > 0)
<nav class="quick-access-navbar">
    <ul class="quick-access-menu">
        @foreach($quickaccess as $rows)
        <li class="quick-access-item">
            <a href="/{{$rows->link}}" class="quick-access-link" title="{{ $rows->description}}">
                <i class="{{$rows->icons}}"></i>
                <span>{{ $rows->description}}</span>
            </a>
        </li>
        @endforeach
    </ul>
    <div class="hamburger-container">
        <div class="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
        <span class="hamburger-text">Mostrar Menú</span>
    </div>
</nav>
@endif
<script>
    const hamburger = document.querySelector(".hamburger");
    const hamburger_text = document.querySelector(".hamburger-text");
    const navMenu = document.querySelector(".quick-access-menu");
    const navLink = document.querySelectorAll(".quick-access-link");
    
    hamburger_text.addEventListener("click", mobileMenu);
    hamburger.addEventListener("click", mobileMenu);
    navLink.forEach(n => n.addEventListener("click", closeMenu));

    function mobileMenu() {
        hamburger.classList.toggle("active");
        navMenu.classList.toggle("active");
    }

    function closeMenu() {
        hamburger.classList.remove("active");
        navMenu.classList.remove("active");
    }
</script>