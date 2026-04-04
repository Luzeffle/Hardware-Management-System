{{--
    Shared sidebar markup used by the main container component.
--}}
<aside
    :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col overflow-y-auto bg-[#050938] px-4 py-6 text-slate-100 transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
>
    <div class="mb-8 flex items-center gap-3 px-2">
        <x-application-logo />
        <div>
            <p class="text-sm font-semibold leading-4">RNM Hardware</p>
            <p class="text-xs text-indigo-200">Management System</p>
        </div>

        <button
            type="button"
            @click="mobileOpen = false"
            class="ms-auto rounded-md p-2 text-indigo-900 hover:bg-white/10 lg:hidden"
            aria-label="Close sidebar"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="space-y-1">
        <x-sidebar.link
            href="{{ route('dashboard') }}"
            :active="request()->routeIs('dashboard')"
            @click="mobileOpen = false"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h7.5v4.5h-7.5v-4.5zm9 0h7.5v10.5h-7.5V6.75zm-9 6h7.5v4.5h-7.5v-4.5z" />
            </svg>
            <span>Dashboard</span>
        </x-sidebar.link>

        @can('pos.access')
        <x-sidebar.dropdown
            label="POS"
            :open="request()->routeIs('pos')"
        >
            <x-slot:icon>
                {{-- POS: card/terminal icon --}}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="4.5" y="5.25" width="15" height="13.5" rx="2.25" ry="2.25" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9h9m-9 4.5h3.75" />
                </svg>
            </x-slot:icon>

            <x-sidebar.item 
                href="{{ route('pos') }}"
                :active="request()->routeIs('pos')"
            >
                New Sale
            </x-sidebar.item>
            <x-sidebar.item href="#">
                Transactions
            </x-sidebar.item>
        </x-sidebar.dropdown>
        @endcan

        <x-sidebar.dropdown
            label="Purchasing"
        >
            <x-slot:icon>
                {{-- POS: card/terminal icon --}}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="4.5" y="5.25" width="15" height="13.5" rx="2.25" ry="2.25" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9h9m-9 4.5h3.75" />
                </svg>
            </x-slot:icon>

            <x-sidebar.item 
                href="#"
            >
                New Invoice
            </x-sidebar.item>
            <x-sidebar.item href="#">
                Invoice History
            </x-sidebar.item>
        </x-sidebar.dropdown>

        <x-sidebar.dropdown
            label="Inventory"
            :open="request()->routeIs('inventory.*')"
        >
            <x-slot:icon>
                {{-- Inventory: box icon --}}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-4.5-9 4.5 9 4.5 9-4.5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5v9l9 4.5 9-4.5v-9" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 12v9" />
                </svg>
            </x-slot:icon>

            <x-sidebar.item href="#">
                Stock Overview
            </x-sidebar.item>
            <x-sidebar.item href="#">
                Manual Stock In
            </x-sidebar.item>
            <x-sidebar.item href="#">
                Stock Out
            </x-sidebar.item>
            <x-sidebar.item href="#">
                Stock Movements
            </x-sidebar.item>
            <x-sidebar.item href="#">
                Archives
            </x-sidebar.item>
        </x-sidebar.dropdown>

        <x-sidebar.dropdown
            label="Audit Logs"
            :open="request()->routeIs('audit-logs.*')"
        >
            <x-slot:icon>
                {{-- Audit Logs: document with check icon --}}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75h6.75L21 9v9.75A2.25 2.25 0 0118.75 21H9A2.25 2.25 0 016.75 18.75V6A2.25 2.25 0 019 3.75z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 12.75l2.25 2.25 3.75-3.75" />
                </svg>
            </x-slot:icon>

            <x-sidebar.item href="#" variant="primary">
                User Activity
            </x-sidebar.item>
            <x-sidebar.item href="#">
                System Logs
            </x-sidebar.item>
            <x-sidebar.item href="#">
                Archives
            </x-sidebar.item>
        </x-sidebar.dropdown>

        <x-sidebar.item href="#" class="flex items-center gap-3">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12.75h16.5M7.5 8.25h9m-9 9h9M4.5 5.25h15A1.5 1.5 0 0121 6.75v10.5a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 17.25V6.75a1.5 1.5 0 011.5-1.5z" />
            </svg>
            <span>Supplier Records</span>
        </x-sidebar.item>

        <x-sidebar.link
            href="{{ route('profile.edit') }}"
            :active="request()->routeIs('profile.*')"
            @click="mobileOpen = false"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 19.5a7.5 7.5 0 0115 0" />
            </svg>
            <span>Users</span>
        </x-sidebar.link>
    </nav>

    <div class="mt-auto">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-indigo-100 transition hover:bg-white/10"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 15l3-3m0 0l-3-3m3 3H9" />
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>
