<nav class="sidebar close">
    <header>
        <div class="image-text">
            <span class="image">
                <!-- <img src="{{ asset('assets/img/logo.png') }}" alt="logo"> -->
            </span>

            <div class="text header-text">
                <span class="name">BMS</span>
                <span class="profession">Backup Management</span>
            </div>
        </div>

        <i class='bx bx-chevron-right toggle'></i>
    </header>

    <div class="menu-bar">
        <div class="menu">

            
            {{--
            <li class="profile">
                <a href="{{ route('backend.profile') }}">
                    <i class='bx bx-user icon'></i>
                    <span class="text nav-text">Profile</span>
                </a>
            </li>
            --}}

            <ul class="menu-link">
                <li class="nav-link">
                    <a href="{{ route('dashboard') }}">
                        <i class='bx bx-home-alt icon'></i>
                        <span class="text nav-text">Dashboard</span>
                    </a>
                </li>
            </ul>

            <ul class="menu-link">
                <li class="nav-link active">
                    <a href="#" class="data-induk">
                        <i class='bx bx-data icon'></i>
                        <span class="text nav-text">Master Data</span>
                        <i class='bx bx-chevron-down arrow-dropdown'></i>
                    </a>
                </li>

                <ul class="sub-menu close">
                    <li><a href="{{ route('departemen.index') }}">Departemen</a></li>
                    <li><a href="{{ route('komputer.index') }}">Data Komputer</a></li>
                    <li><a href="{{ route('laptop.index') }}">Data Laptop</a></li>
                    <li><a href="{{ route('stok.index') }}">Stok CD/DVD</a></li>
                </ul>
            </ul>
            <ul class="menu-link">
                <li class="nav-link active">
                    <a href="#" class="data-induk">
                        <i class='bx bx-folder-open icon'></i>
                        <span class="text nav-text">Rekap Backup</span>
                        <i class='bx bx-chevron-down arrow-dropdown'></i>
                    </a>
                </li>

                <ul class="sub-menu close">
                    <li><a href="{{ url('/rekap-backup') }}">Input Size Backup</a></li>
                    <li><a href="{{ route('rekap-backup.cd-dvd') }}"> Penggunaan CD/DVD</a></li>
                </ul>
            </ul>

            <ul class="menu-link">
                <li class="nav-link active">
                    <a href="#" class="data-induk">
                        <i class='bx bx-file icon'></i>
                        <span class="text nav-text">Laporan</span>
                        <i class='bx bx-chevron-down arrow-dropdown'></i>
                    </a>
                </li>


                <ul class="sub-menu close">
                    <li><a href="{{ route('departemen.index') }}">Bulanan</a></li>
                    <li><a href="{{ route('komputer.index') }}">Perusahaan</a></li>
                    <li><a href="{{ route('laptop.index') }}">Departemen</a></li>
                </ul>
            </ul>


        </div>

        <!-- <div class="bottom-content">
            <li class="logout">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Logout</span>
                    </button>
                </form>
            </li>
        </div> -->
    </div>
</nav>
