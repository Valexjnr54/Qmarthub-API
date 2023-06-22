<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
      <div class="sidebar-brand">
        <a href="index.html"> <img alt="image" src="assets/img/logo.png" class="header-logo" /> <span
            class="logo-name">Otika</span>
        </a>
      </div>
      <ul class="sidebar-menu">
        <li class="menu-header">Main</li>
        <li class="dropdown">
          <a href="{{ route('admin.dashboard') }}" class="nav-link"><i data-feather="monitor"></i><span>Dashboard</span></a>
        </li>
        <li class="dropdown">
            <a href="{{ route('admin.category') }}" class="nav-link"><i data-feather="monitor"></i><span>Categories</span></a>
        </li>
        <li class="dropdown">
            <a href="{{ route('admin.brand') }}" class="nav-link"><i data-feather="monitor"></i><span>Brand</span></a>
        </li>
        <li class="dropdown">
            <a href="{{ route('admin.drink') }}" class="nav-link"><i data-feather="monitor"></i><span>Drinks</span></a>
        </li>
        <li class="dropdown">
            <a href="{{ route('admin.extra') }}" class="nav-link"><i data-feather="monitor"></i><span>Toppings</span></a>
        </li>
          <li class="dropdown">
            <a href="{{ route('admin.bulk') }}" class="nav-link"><i data-feather="monitor"></i><span>Bulk</span></a>
          </li>
          <li class="dropdown">
            <a href="{{ route('admin.product') }}" class="nav-link"><i data-feather="monitor"></i><span>Product</span></a>
          </li>
        <li class="dropdown">
          <a href="#" class="menu-toggle nav-link has-dropdown"><i
              data-feather="briefcase"></i><span>Foods / Vendors</span></a>
          <ul class="dropdown-menu">
            <li><a class="nav-link" href="{{ route('admin.food') }}">Foods</a></li>
            <li><a class="nav-link" href="{{ route('admin.foodVendor') }}">Food Vendors</a></li>
          </ul>
        </li>
        <li class="dropdown">
            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                data-feather="briefcase"></i><span>Orders</span></a>
            <ul class="dropdown-menu">
              <li><a class="nav-link" href="{{ route('admin.foodOrderDetails') }}">Food Orders</a></li>
              <li><a class="nav-link" href="{{ route('admin.orderDetails') }}">Glocery Orders</a></li>
              <li><a class="nav-link" href="{{ route('admin.bulkBuyOrderDetails') }}">Bulk Orders</a></li>
            </ul>
          </li>
          <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                data-feather="briefcase"></i><span>Receipts</span></a>
            <ul class="dropdown-menu">
              <li><a href="{{ route('admin.receipt') }}" class="nav-link">Receipts</a></li>
              <li><a class="nav-link" href="{{ route('admin.bulk-receipt') }}">Bulk Buy Receipts</a></li>
            </ul>
          </li>
          <li class="dropdown">
            <a href="{{ route('logout') }}" style="color:red" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-link"><i data-feather="log-out"></i><span>{{ __('Logout') }}</span></a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
          </li>
      </ul>
    </aside>
  </div>
