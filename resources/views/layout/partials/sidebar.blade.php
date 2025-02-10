<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
				
				<li class="submenu-open">
                    <h6 class="submenu-hdr">Main</h6>
                    <ul>
						<li><a href="{{ url('index') }}" class="{{ Request::is('index', '/') ? 'active' : '' }}">
							<i data-feather="home"></i>Dashboard</a></li>
						<li class="submenu">
							<a href="javascript:void(0);"
								class="{{ Request::is('suppliers', 'warehouse', 'store-list', 'customers', 'category-list', 'subcategory-list', 'ssubcategory-list', 'sssubcategory-list', 'brand-list', 'size') ? 'active subdrop' : '' }}"><i
									data-feather="key"></i><span>Master</span><span
									class="menu-arrow"></span></a>
							<ul>
								<li class="{{ Request::is('suppliers') ? 'active' : '' }}"><a
									href="{{ url('suppliers') }}"><span>Suppliers</span></a>
								</li>
								<li class="{{ Request::is('customers') ? 'active' : '' }}"><a
									href="{{ url('customers') }}"><span>Members</span></a>
								<li class="{{ Request::is('warehouse') ? 'active' : '' }}"><a
									href="{{ url('warehouse') }}"><span>Warehouse</span></a>
								</li>
								<li class="{{ Request::is('store-list') ? 'active' : '' }}"><a
									href="{{ url('store-list') }}"><span>Stores</span></a>
								</li>
								<li class="submenu submenu-two submenu-three"><a
									href="javascript:void(0);" style="color: #5B6670;">Product<span
										class="menu-arrow inside-submenu inside-submenu-two"></span></a>
									<ul>
										<li class="{{ Request::is('category-list') ? 'active' : '' }}"><a
											href="{{ url('category-list') }}"><span>Category</span></a></li>
										<li class="{{ Request::is('subcategory-list') ? 'active' : '' }}"><a
											href="{{ url('subcategory-list') }}"><span>Sub Category</span></a></li>
										<li class="{{ Request::is('ssubcategory-list') ? 'active' : '' }}"><a
												href="{{ url('ssubcategory-list') }}"><span>Sub-Sub Category</span></a></li>
										<li class="{{ Request::is('sssubcategory-list') ? 'active' : '' }}"><a
											href="{{ url('sssubcategory-list') }}"><span>Sub-Sub-Sub Category</span></a></li>
										<li class="{{ Request::is('brand-list') ? 'active' : '' }}"><a
											href="{{ url('brand-list') }}"><span>Brands</span></a></li>
										</li>
										<li class="{{ Request::is('size') ? 'active' : '' }}"><a
											href="{{ url('size') }}"><span>Size</span></a></li>
										</li>
									</ul>
								</li>
							</ul>
						</li>
					</ul>
				</li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Inventory</h6>
                    <ul>
						<li class="submenu">
							<a href="javascript:void(0);"
								class="{{ Request::is('product-list', 'low-stocks') ? 'active subdrop' : '' }}"><i data-feather="box"></i><span>Products</span><span class="menu-arrow"></span></a>
							<ul>
								<li class="{{ Request::is('product-list') ? 'active' : '' }}"><a
									href="{{ url('product-list') }}"><span>Product Lists</span></a>
								</li>
								<li class="{{ Request::is('low-stocks') ? 'active' : '' }}"><a
									href="{{ url('low-stocks') }}"><span>Low Stocks</span></a></li>
                    		</ul>
						</li>
						<li class="submenu">
							<a href="javascript:void(0);"
								class="{{ Request::is('manage-stocks', 'stock-adjustment', 'stock-transfer') ? 'active subdrop' : '' }}"><i data-feather="layers"></i><span>Stock</span><span class="menu-arrow"></span></a>
							<ul>
								<li class="{{ Request::is('manage-stocks') ? 'active' : '' }}"><a
									href="{{ url('manage-stocks') }}"><span>Manage
										Stock</span></a></li>
							<!---- <li class="{{ Request::is('stock-adjustment') ? 'active' : '' }}"><a
									href="{{ url('stock-adjustment') }}"><span>Stock
										Opname</span></a></li> -->
							<li class="{{ Request::is('stock-transfer') ? 'active' : '' }}"><a
									href="{{ url('stock-transfer') }}"><span>Stock
										Transfer</span></a></li>
							</ul>
						</li>
					</ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Purchases</h6>
                    <ul>
                        <li class="{{ Request::is('purchase-order') ? 'active' : '' }}"><a
                            href="{{ url('purchase-order') }}"><i data-feather="shopping-bag"></i><span>Purchase Order</span></a></li>
                        <li class="{{ Request::is('purchase-received') ? 'active' : '' }}"><a
                            href="{{ url('purchase-received') }}"><i data-feather="file-text"></i><span>Purchase Received</span></a></li>
                    </ul>
                </li>
                <!-- <li class="submenu-open">
                    <h6 class="submenu-hdr">Promo</h6>
                    <ul>
                        <li class="{{ Request::is('coupons') ? 'active' : '' }}"><a href="{{ url('coupons') }}"><i
                                    data-feather="tag"></i><span>Coupons</span></a>
                        </li>
                    </ul>
                </li> -->
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Sales</h6>
                    <ul>
                        <!--<li class="{{ Request::is('sales-list') ? 'active' : '' }}"><a href="{{ url('sales-list') }}"><i data-feather="shopping-cart"></i><span>Sales</span></a></li>
                        <li class="{{ Request::is('invoice-report') ? 'active' : '' }}"><a href="{{ url('invoice-report') }}"><i data-feather="file-text"></i><span>Invoices</span></a></li>-->
						<li class="{{ Request::is('pos') ? 'active' : '' }}"><a href="{{ url('pos') }}"><i data-feather="hard-drive"></i><span>POS</span></a></li>
                        <li class="{{ Request::is('sales-returns') ? 'active' : '' }}"><a href="{{ url('sales-returns') }}"><i data-feather="copy"></i><span>Sales Return</span></a></li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Finance</h6>
                    <ul>
						<li><a href="{{ url('expense-category') }}"
							class="{{ Request::is('expense-category') ? 'active' : '' }}"><span>Expense
							Category</span></a></li>
						<li><a href="{{ url('expense-list') }}" class="{{ Request::is('expense-list') ? 'active' : '' }}"><i data-feather="file-text"></i>Expenses</a></li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Reports</h6>
                    <ul>
                        <li class="{{ Request::is('sales-report') ? 'active' : '' }}"><a
							href="{{ url('sales-report') }}"><i data-feather="bar-chart-2"></i><span>Sales
								Report</span></a></li>
                        <li class="{{ Request::is('purchase-report') ? 'active' : '' }}"><a
							href="{{ url('purchase-report') }}"><i data-feather="pie-chart"></i><span>Purchase
								report</span></a></li>
                        <li class="{{ Request::is('inventory-report') ? 'active' : '' }}"><a
							href="{{ url('inventory-report') }}"><i data-feather="inbox"></i><span>Inventory
								Report</span></a></li>
                        <!--<li class="{{ Request::is('invoice-report') ? 'active' : '' }}"><a
							href="{{ url('invoice-report') }}"><i data-feather="file"></i><span>Invoice
								Report</span></a></li>
                        <li class="{{ Request::is('supplier-report') ? 'active' : '' }}"><a
							href="{{ url('supplier-report') }}"><i data-feather="user-check"></i><span>Supplier
								Report</span></a></li>
                        <li class="{{ Request::is('customer-report') ? 'active' : '' }}"><a
							href="{{ url('customer-report') }}"><i data-feather="user"></i><span>Customer
								Report</span></a></li>
                        <li class="{{ Request::is('expense-report') ? 'active' : '' }}"><a
							href="{{ url('expense-report') }}"><i data-feather="file"></i><span>Expense
								Report</span></a></li>
                        <li class="{{ Request::is('income-report') ? 'active' : '' }}"><a
							href="{{ url('income-report') }}"><i data-feather="bar-chart"></i><span>Income
								Report</span></a></li>-->
                        <li class="{{ Request::is('profit-and-loss') ? 'active' : '' }}"><a
							href="{{ url('profit-and-loss') }}"><i data-feather="pie-chart"></i><span>Profit &
								Loss</span></a></li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">User Management</h6>
                    <ul>
                        <li class="{{ Request::is('users') ? 'active' : '' }}"><a href="{{ url('users') }}"><i
							data-feather="user-check"></i><span>Users</span></a>
                        </li>
                        <li class="{{ Request::is('roles-permissions','permissions') ? 'active' : '' }}"><a
							href="{{ url('roles-permissions') }}"><i data-feather="shield"></i><span>Roles &
								Permissions</span></a></li>
                    </ul>
                </li>
                <li class="submenu-open">
					<h6 class="submenu-hdr">Profile</h6>
					<li class="{{ Request::is('signin') ? 'active' : '' }}">
						<a href="{{ url('signin') }}"><i data-feather="log-out"></i><span>Logout</span> </a>
					</li>
				</li>
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->
