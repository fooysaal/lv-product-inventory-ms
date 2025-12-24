<template>
    <nav class="space-y-1">
        <!-- Dashboard -->
        <MenuItem
            to="/dashboard"
            icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
            label="Dashboard"
        />

        <!-- Product Management -->
        <MenuSection title="Products">
            <MenuItem
                to="/products"
                icon="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                label="Products"
            />
            <MenuItem
                to="/categories"
                icon="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                label="Categories"
            />
        </MenuSection>

        <!-- Warehouse Management -->
        <MenuSection title="Warehouses">
            <MenuItem
                to="/warehouses"
                icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                label="My Warehouses"
            />
        </MenuSection>

        <!-- Stock Management -->
        <MenuSection title="Stock Management">
            <MenuItem
                to="/stock-ins"
                icon="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
                label="Stock In"
            />
            <MenuItem
                to="/stock-outs"
                icon="M16 17l-4 4m0 0l-4-4m4 4V3"
                label="Stock Out"
            />
            <MenuItem
                to="/stock-balance"
                icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                label="Stock Balance"
            />
            <MenuItem
                to="/approvals"
                icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                label="Pending Approvals"
            />
        </MenuSection>

        <!-- Reports -->
        <MenuSection title="Reports">
            <MenuItem
                to="/reports/inventory"
                icon="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                label="Inventory Report"
            />
            <MenuItem
                to="/reports/stock-movement"
                icon="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"
                label="Stock Movement"
            />
            <MenuItem
                to="/reports/low-stock"
                icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                label="Low Stock Alert"
            />
        </MenuSection>
    </nav>
</template>

<script setup>
import MenuItem from '@/components/Menus/MenuItem.vue';
import MenuSection from '@/components/Menus/MenuSection.vue';
</script>
