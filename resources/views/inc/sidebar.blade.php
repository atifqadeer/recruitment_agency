<!-- Main sidebar -->
<div class="sidebar sidebar-dark sidebar-main sidebar-expand-md">

    <!-- Sidebar mobile toggler -->
    <div class="sidebar-mobile-toggler text-center">
        <a href="#" class="sidebar-mobile-main-toggle">
            <i class="icon-arrow-left8"></i>
        </a>
        Navigation
        <a href="#" class="sidebar-mobile-expand">
            <i class="icon-screen-full"></i>
            <i class="icon-screen-normal"></i>
        </a>
    </div>
    <!-- /sidebar mobile toggler -->


    <!-- Sidebar content -->
    <div class="sidebar-content">
        <!-- Main navigation -->
        <div class="card card-sidebar-mobile">
            <ul class="nav nav-sidebar" data-nav-type="accordion">
                @can('dashboard_statistics')
					<li class="nav-item {{Route::is('home')? 'active_menu' : '' }}">
						<a href="{{ route('home') }}" class="nav-link">
							<i class="icon-home4"></i><span>Dashboard</span>
						</a>
					</li>
                @endcan
			@canany(['applicant_list','applicant_import','applicant_create','applicant_edit','applicant_view','applicant_history','applicant_note-create','applicant_note-history'])
					<li class="nav-item {{ Route::is('applicants.index') || Route::is('applicantHistory') || Route::is('applicants.show') || Route::is('applicants.edit') || Route::is('applicants.export_csv') || Route::is('applicants.create') || Route::is('applicants.export_email')? 'active_menu' : '' }}">
						<a href="{{ route('applicants.index') }}" class="nav-link">
							<i class="icon-users4"></i>
							<span>Applicants</span>
						</a>
					</li>
                @endcanany
			
                @canany(['user_list','user_create','user_edit','user_enable-disable','user_activity-log'])
                <li class="nav-item {{Route::is('users.index') || Route::is('activityLogs') || Route::is('users.create') || Route::is('users.edit')? 'active_menu' : '' }}">
                    <a href="{{ route('users.index') }}" class="nav-link">
                        <i class="icon-users2"></i>
                        <span>Users</span>
                    </a>
                </li>
                @endcanany
                @canany(['office_list','office_import','office_create','office_edit','office_view','office_note-history','office_note-create'])
                <li class="nav-item {{Route::is('offices.index') || Route::is('offices.create') || Route::is('offices.show') || Route::is('offices.edit') ? 'active_menu' : '' }}">
                    <a href="{{ route('offices.index') }}" class="nav-link">
                        <i class="icon-office"></i>
                        <span>Head Offices</span>
                    </a>
                </li>
                @endcanany
                @canany(['unit_list','unit_import','unit_create','unit_edit','unit_view','unit_note-create','unit_note-history'])
                <li class="nav-item {{ Route::is('units.index') || Route::is('units.create') || Route::is('units.edit') || Route::is('units.show')? 'active_menu' : '' }}">
                    <a href="{{ route('units.index') }}" class="nav-link">
                        <i class="icon-underline2"></i>
                        <span>Units</span>
                    </a>
                </li>
                @endcanany
                @canany(['sale_list','sale_import','sale_create','sale_edit','sale_view','sale_open','sale_close','sale_manager-detail','sale_history','sale_notes','sale_note-create','sale_note-history','sale_closed-sales-list','sale_closed-sale-notes','sale_psl-offices-list','sale_psl-office-details','sale_psl-office-units','sale_non-psl-offices-list','sale_non-psl-office-details','sale_non-psl-office-units'])
                <li class="nav-item nav-item-submenu  {{ Route::is('nonpsl') || Route::is('psl') || Route::is('on_hold_sales_data') || Route::is('onhold-sale-history') || Route::is('viewAllCloseNotes') || Route::is('viewAllOpenNotes') || Route::is('sales.create') || Route::is('saleHistory') || Route::is('sales.show') || Route::is('sales.edit') || Route::is('sales.create') || Route::is('close_sales') || Route::is('sales.index') ?'nav-item-open':'' }}">
                    <a href="#" class="nav-link"><i class="icon-stats-growth"></i> <span>Sales</span></a>
                    	<ul class="nav nav-group-sub" data-submenu-title="Sales" {{ Route::is('nonpsl') || Route::is('psl') || Route::is('on_hold_sales_data') || Route::is('onhold-sale-history') || Route::is('viewAllCloseNotes') || Route::is('viewAllOpenNotes') || Route::is('close_sales') || Route::is('saleHistory') || Route::is('sales.show') || Route::is('sales.edit') || Route::is('sales.create') || Route::is('sales.index') ?'style=display:block':'' }}>
							@canany(['Region_All'])
                                <li class="nav-item nav-item-submenu">
                                    <a href="#" class="nav-link"><i class="icon-circle-small"></i><span>Regions</span></a>
                                    	<ul class="nav nav-group-sub" data-submenu-title="Quality">
                                            <li class="nav-item nav-item-submenu">
                                                <a href="#" class="nav-link" id="1"><i class="icon-city"></i>Scotland</a>
                                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                    <li class="nav-item nav-item-submenu">
                                                        <a href="#" id="1"
                                                           class="nav-link">
                                                            Sales
                                                        </a>
                                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '1','category' => '44']) }}" id="1"
                                                                   class="nav-link">
                                                                    Nurse
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '1','category' => '45']) }}" id="1"
                                                                   class="nav-link">
                                                                    Non-Nurse
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li class="nav-item nav-item-submenu">
                                                <a href="#" class="nav-link" id="2"><i class="icon-city" ></i>Northern Ireland</a>
                                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                    <li class="nav-item nav-item-submenu">
                                                        <a href="#" id="2"
                                                           class="nav-link">
                                                            Sales
                                                        </a>
                                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                            <li class="nav-item">
                                                                <a href="{{ route('region.nurses.sales', ['id' => '2','category' => '44']) }}" id="2"
                                                                   class="nav-link">
                                                                    Nurse
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="{{ route('region.nurses.sales', ['id' => '2','category' => '45']) }}" id="2"
                                                                   class="nav-link">
                                                                    Non-Nurse
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li class="nav-item nav-item-submenu">
                                                <a href="#" class="nav-link" id="3"><i class="icon-city" ></i>Wales</a>
                                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                    <li class="nav-item nav-item-submenu">
                                                        <a href="#" id="3"
                                                           class="nav-link">
                                                            Sales
                                                        </a>
                                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '3','category' => '44']) }}" id="3"
                                                                   class="nav-link">
                                                                    Nurse
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '3','category' => '45']) }}" id="3"
                                                                   class="nav-link">
                                                                    Non-Nurse
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li class="nav-item nav-item-submenu">
                                                <a href="#" class="nav-link" id="4"><i class="icon-city" ></i>North East</a>
                                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                    <li class="nav-item nav-item-submenu">
                                                        <a href="#" id="4"
                                                           class="nav-link">
                                                            Sales
                                                        </a>
                                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '4','category' => '44']) }}" id="4"
                                                                   class="nav-link">
                                                                    Nurse
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '4','category' => '45']) }}" id="4"
                                                                   class="nav-link">
                                                                    Non-Nurse
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>


                                            <li class="nav-item nav-item-submenu">
                                                <a href="#" class="nav-link" id="5"><i class="icon-city" ></i>North West</a>
                                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                    <li class="nav-item nav-item-submenu">
                                                        <a href="#" id="5"
                                                           class="nav-link">
                                                            Sales
                                                        </a>
                                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '5','category' => '44']) }}" id="5"
                                                                   class="nav-link">
                                                                    Nurse
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '5','category' => '45']) }}" id="5"
                                                                   class="nav-link">
                                                                    Non-Nurse
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li class="nav-item nav-item-submenu">
                                                <a href="#" class="nav-link" id="6"><i class="icon-city" ></i>West Midlands</a>
                                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                    <li class="nav-item nav-item-submenu">
                                                        <a href="#" id="6"
                                                           class="nav-link">
                                                            Sales
                                                        </a>
                                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '6','category' => '44']) }}" id="6"
                                                                   class="nav-link">
                                                                    Nurse
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '6','category' => '45']) }}" id="6"
                                                                   class="nav-link">
                                                                    Non-Nurse
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li class="nav-item nav-item-submenu">
                                                <a href="#" class="nav-link" id="7"><i class="icon-city" ></i>East Midlands</a>
                                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                    <li class="nav-item nav-item-submenu">
                                                        <a href="#" id="7"
                                                           class="nav-link">
                                                            Sales
                                                        </a>
                                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '7','category' => '44']) }}" id="7"
                                                                   class="nav-link">
                                                                    Nurse
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '7','category' => '45']) }}" id="7"
                                                                   class="nav-link">
                                                                    Non-Nurse
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li class="nav-item nav-item-submenu">
                                                <a href="#" class="nav-link" id="8"><i class="icon-city" ></i>South West</a>
                                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                    <li class="nav-item nav-item-submenu">
                                                        <a href="#" id="8"
                                                           class="nav-link">
                                                            Sales
                                                        </a>
                                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '8','category' => '44']) }}" id="8"
                                                                   class="nav-link">
                                                                    Nurse
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '8','category' => '45']) }}" id="8"
                                                                   class="nav-link">
                                                                    Non-Nurse
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li class="nav-item nav-item-submenu">
                                                <a href="#" class="nav-link" id="9"><i class="icon-city" ></i>South East</a>
                                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                    <li class="nav-item nav-item-submenu">
                                                        <a href="#" id="9"
                                                           class="nav-link">
                                                            Sales
                                                        </a>
                                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '9','category' => '44']) }}" id="9"
                                                                   class="nav-link">
                                                                    Nurse
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '9','category' => '45']) }}" id="9"
                                                                   class="nav-link">
                                                                    Non-Nurse
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li class="nav-item nav-item-submenu">
                                                <a href="#" class="nav-link" id="10"><i class="icon-city" ></i>East of England</a>
                                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                    <li class="nav-item nav-item-submenu">
                                                        <a href="#" id="10"
                                                           class="nav-link">
                                                            Sales
                                                        </a>
                                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '10','category' => '44']) }}" id="10"
                                                                   class="nav-link">
                                                                    Nurse
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '10','category' => '45']) }}" id="10"
                                                                   class="nav-link">
                                                                    Non-Nurse
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li class="nav-item nav-item-submenu">
                                                <a href="#" class="nav-link" id="11"><i class="icon-city" ></i>Greater London</a>
                                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                    <li class="nav-item nav-item-submenu">
                                                        <a href="#" id="11"
                                                           class="nav-link">
                                                            Sales
                                                        </a>
                                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '11','category' => '44']) }}" id="11"
                                                                   class="nav-link">
                                                                    Nurse
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '11','category' => '45']) }}" id="11"
                                                                   class="nav-link">
                                                                    Non-Nurse
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li class="nav-item nav-item-submenu">
                                                <a href="#" class="nav-link" id="12"><i class="icon-city" ></i>Common Regions</a>
                                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                    <li class="nav-item nav-item-submenu">
                                                        <a href="#" id="12"
                                                           class="nav-link">
                                                            Sales
                                                        </a>
                                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '12','category' => '44']) }}" id="12"
                                                                   class="nav-link">
                                                                    Nurse
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="{{ url('region-nurses-close-sales', ['id' => '12','category' => '45']) }}" id="12"
                                                                   class="nav-link">
                                                                    Non-Nurse
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                    </ul>
                                </li>
                            @endcanany

                            @canany(['sale_list','sale_import','sale_create','sale_edit','sale_view','sale_close','sale_manager-detail','sale_history','sale_notes','sale_note-create','sale_note-history'])
                                <li class="nav-item {{ Route::is('sales.index') || Route::is('viewAllOpenNotes') || Route::is('sales.show') || Route::is('saleHistory') || Route::is('sales.create') || Route::is('sales.edit') ?'active_menu':'' }}">
                                    <a href="{{ route('sales.index') }}" class="nav-link"><i class="icon-circle-small"></i>Open Sales</a>
                                </li>
                            @endcanany
                            @canany(['sale_closed-sales-list','sale_open','sale_closed-sale-notes'])
                            <li class="nav-item {{ Route::is('viewAllCloseNotes') || Route::is('close_sales')?'active_menu':'' }}">
                                <a href="{{ route('close_sales') }}" class="nav-link"><i class="icon-circle-small"></i>Close Sales</a>
                            </li>
                            @endcanany
                            @canany(['sale_on-hold'])
                            <li class="nav-item {{ Route::is('on_hold_sales_data') || Route::is('onhold-sale-history') ? 'active_menu' : '' }}">
                                <a href="{{ route('on_hold_sales_data') }}" class="nav-link"><i class="icon-circle-small"></i>On Hold Sales</a>
                            </li>
                            @endcanany
                            @canany(['pending_on_hold_sales'])
                            <li class="nav-item {{ Route::is('pending.onhold_sales') || Route::is('pending.onhold_sales') ? 'active_menu' : '' }}">
                                <a href="{{ route('pending.onhold_sales') }}" class="nav-link"><i class="icon-circle-small"></i>Pending On Hold Sales</a>
                            </li>
                            @endcanany
                            @canany(['sale_psl-offices-list','sale_psl-office-details','sale_psl-office-units'])
                            <li class="nav-item {{ Route::is('psl') ? 'active_menu':'' }}">
                                <a href="{{ route('psl') }}" class="nav-link"><i class="icon-circle-small"></i>PSL Clients</a>
                            </li>
                            @endcanany
                            @canany(['sale_non-psl-offices-list','sale_non-psl-office-details','sale_non-psl-office-units'])
                            <li class="nav-item {{ Route::is('nonpsl') ? 'active_menu':'' }}">
                                <a href="{{ route('nonpsl') }}" class="nav-link"><i class="icon-circle-small"></i>Non PSL Clients</a>
                            </li>
                            @endcanany
                        </ul>
                </li>
                @endcanany
                @canany(['resource_Nurses-list','resource_Non-Nurses-list','resource_Last-7-Days-Applicants','resource_Last-21-Days-Applicants','resource_All-Applicants','resource_Crm-Rejected-Applicants','resource_Crm-Request-Rejected-Applicants','resource_Crm-Not-Attended-Applicants','resource_Crm-Start-Date-Hold-Applicants','resource_No-Nursing-Home_list','resource_No-Nursing-Home_revert-no-nursing-home','resource_Non-Interested-Applicants','resource_Crm-Paid-Applicants','resource_Potential-Callback_list','resource_Potential-Callback_revert-callback','resource_Non-Nurses-specialist','applicant_no-job'])
                <li class="nav-item nav-item-submenu {{ (request()->is('rejected-app-date-wise/44/all')) || (request()->is('rejected-app-date-wise/44/9')) || (request()->is('rejected-app-date-wise/44/6')) || (request()->is('rejected-app-date-wise/44/3')) || Route::is('getDirectNurse') || Route::is('getDirectChef') || Route::is('getDirectNonNurse') ||  Route::is('getDirectNonNurseSpecialist') || Route::is('get_no_job_applicants') ||  Route::is('nonInterestedApplicants') ||  Route::is('potential-call-back-applicants') ||  Route::is('nurseHomeApplicants') ||  Route::is('crmPaidCv') ||  Route::is('last2monthsBlockedApplicants') ?'nav-item-open':'' }}">
                    <a href="#" class="nav-link"><i class="icon-folder-search"></i> <span>Resources</span></a>
                    <ul class="nav nav-group-sub" data-submenu-title="Resource" {{  (request()->is('rejected-app-date-wise/44/all')) || (request()->is('rejected-app-date-wise/44/9')) || (request()->is('rejected-app-date-wise/44/6')) || (request()->is('rejected-app-date-wise/44/3')) || Route::is('getDirectNurse') || Route::is('getDirectChef') || Route::is('getDirectNonNurse') ||  Route::is('getDirectNonNurseSpecialist') ||  Route::is('get_no_job_applicants') ||  Route::is('nonInterestedApplicants') ||  Route::is('potential-call-back-applicants') ||  Route::is('nurseHomeApplicants') ||  Route::is('crmPaidCv') ||  Route::is('last2monthsBlockedApplicants') ?'style=display:block':'' }}>
                        @canany(['resource_Nurses-list','resource_Non-Nurses-list','resource_Non-Nurses-specialist'])
                        <li class="nav-item nav-item-submenu {{ Route::is('getDirectNurse') || Route::is('getDirectChef') || Route::is('getDirectNonNurse') ||  Route::is('getDirectNonNurseSpecialist') ?'nav-item-open':'' }}">
                            <a href="#" class="nav-link"><i class="icon-circle-small"></i>Direct</a>
                            <ul class="nav nav-group-sub" data-submenu-title="Direct" {{ Route::is('getDirectNurse') || Route::is('getDirectChef') || Route::is('getDirectNonNurse') ||  Route::is('getDirectNonNurseSpecialist') ?'style=display:block':'' }}>
                                @can('resource_Nurses-list')
                                <li class="nav-item {{ Route::is('getDirectNurse') ?'active_menu':'' }}"><a href="{{ route('getDirectNurse') }}" class="nav-link"><i class="icon-dash"></i>Nurse</a></li>
                                @endcan
                                @can('resource_Non-Nurses-list')
                                <li class="nav-item {{ Route::is('getDirectNonNurse') ?'active_menu':'' }}"><a href="{{ route('getDirectNonNurse') }}" class="nav-link"><i class="icon-dash"></i>Non Nurse</a></li>
                                @endcan
								@can('resource_Non-Nurses-specialist')
                                <li class="nav-item {{ Route::is('getDirectNonNurseSpecialist') ?'active_menu':'' }}"><a href="{{ route('getDirectNonNurseSpecialist') }}" class="nav-link"><i class="icon-dash"></i>Specialist</a></li>
                                @endcan
								 <li class="nav-item {{ Route::is('getDirectChef') ?'active_menu':'' }}"><a href="{{ route('getDirectChef') }}" class="nav-link"><i class="icon-dash"></i>Chef</a></li>
                            </ul>
                        </li>
                        @endcanany
						
                        <li class="nav-item nav-item-submenu">
							<a href="#" class="nav-link"><i class="icon-circle-small"></i><span>Nurse</span></a>
							<ul class="nav nav-group-sub" data-submenu-title="rejected">
								@can('resource_Last-7-Days-Applicants')
								<li class="nav-item"><a href="{{ url('applicants-added-in-last-7-days',['category' => '44']) }}" class="nav-link"><i class="icon-dash"></i>Last 7 Days Applicants</a></li>
{{--                                                <li class="nav-item"><a href="{{ route('last7days',['category' => '44']) }}" class="nav-link"><i class="icon-dash"></i>Last 7 Days Applicants</a></li>--}}
                                                @endcan
                                                    @can('resource_Last-21-Days-Applicants')
                                                    <li class="nav-item"><a href="{{ url('applicants-added-in-last-21-days',['category' => '44'])}}" class="nav-link"><i class="icon-dash"></i>Last 21 Days Applicants</a></li>
                                                @endcan
                                                @can('resource_All-Applicants')
{{--                                                    <li class="nav-item"><a href="{{ route('last2months') }}" class="nav-link">All Applicants</a></li>--}}
                                                    <li class="nav-item"><a href="{{ url('applicants-added-in-last-2-months',['category' => '44']) }}" class="nav-link"><i class="icon-dash"></i>All Applicants</a></li>
                                                @endcan
                                            </ul>
                                        </li>

                                        <li class="nav-item nav-item-submenu">
                                            <a href="#" class="nav-link"><i class="icon-circle-small"></i><span>Non Nurse</span></a>
                                            <ul class="nav nav-group-sub" data-submenu-title="rejected">
                                                @can('resource_Last-7-Days-Applicants')
                                                    <li class="nav-item"><a href="{{ url('applicants-added-in-last-7-days',['category' => '45']) }}" class="nav-link"><i class="icon-dash"></i>Last 7 Days Applicants</a></li>

{{--                                                    <li class="nav-item"><a href="{{ route('last7days') }}" class="nav-link">Last 7 Days Applicants</a></li>--}}
                                                @endcan
                                                @can('resource_Last-21-Days-Applicants')
{{--                                                    <li class="nav-item"><a href="{{ route('last21days') }}" class="nav-link">Last 21 Days Applicants</a></li>--}}
                                                    <li class="nav-item"><a href="{{ url('applicants-added-in-last-21-days',['category' => '45']) }}" class="nav-link"><i class="icon-dash"></i>Last 21 Days Applicants</a></li>
                                                @endcan
                                                @can('resource_All-Applicants')
                                                    <li class="nav-item"><a href="{{  url('applicants-added-in-last-2-months',['category' => '45']) }}" class="nav-link"><i class="icon-dash"></i>All Applicants</a></li>
                                                @endcan
                                            </ul>
                                        </li>


                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" class="nav-link"><i class="icon-circle-small"></i><span>Specialist</span></a>
                                        <ul class="nav nav-group-sub" data-submenu-title="rejected">
                                            @can('resource_Last-7-Days-Applicants')
                                                <li class="nav-item"><a href="{{ url('applicants-added-in-last-7-days',['category' => '46']) }}" class="nav-link"><i class="icon-dash"></i>Last 7 Days Applicants</a></li>

{{--                                                <li class="nav-item"><a href="{{ route('last7days') }}" class="nav-link"><i class="icon-dash"></i>Last 7 Days Applicants</a></li>--}}
                                            @endcan
                                            @can('resource_Last-21-Days-Applicants')
                                                <li class="nav-item"><a href="{{ url('applicants-added-in-last-21-days',['category' => '46']) }}" class="nav-link"><i class="icon-dash"></i>Last 21 Days Applicants</a></li>
                                            @endcan
                                            @can('resource_All-Applicants')
                                                <li class="nav-item"><a href="{{  url('applicants-added-in-last-2-months',['category' => '46']) }}" class="nav-link"><i class="icon-dash"></i>All Applicants</a></li>
                                            @endcan
                                        </ul>
                                    </li>
						 
                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" class="nav-link"><i class="icon-circle-small"></i><span>Chef</span></a>
                                        <ul class="nav nav-group-sub" data-submenu-title="rejected">
                                            @can('resource_Last-7-Days-Applicants')
                                                <li class="nav-item"><a href="{{ url('applicants-added-in-last-7-days',['category' => '47']) }}" class="nav-link"><i class="icon-dash"></i>Last 7 Days Applicants</a></li>

                                                {{--                                                <li class="nav-item"><a href="{{ route('last7days') }}" class="nav-link">Last 7 Days Applicants</a></li>--}}
                                            @endcan
                                            @can('resource_Last-21-Days-Applicants')
                                                <li class="nav-item"><a href="{{ url('applicants-added-in-last-21-days',['category' => '47']) }}" class="nav-link"><i class="icon-dash"></i>Last 21 Days Applicants</a></li>
                                            @endcan
                                            @can('resource_All-Applicants')
                                                <li class="nav-item"><a href="{{  url('applicants-added-in-last-2-months',['category' => '47']) }}" class="nav-link"><i class="icon-dash"></i>All Applicants</a></li>
                                            @endcan
                                        </ul>
                                    </li>
                                    @can('resource_Crm-All-Rejected-Applicants')
                                    <li class="nav-item nav-item-submenu {{ (request()->is('rejected-app-date-wise/45/all')) || (request()->is('rejected-app-date-wise/45/9')) || (request()->is('rejected-app-date-wise/45/6')) || (request()->is('rejected-app-date-wise/45/3')) || (request()->is('rejected-app-date-wise/44/all')) || (request()->is('rejected-app-date-wise/44/9')) || (request()->is('rejected-app-date-wise/44/6')) || (request()->is('rejected-app-date-wise/44/3'))  ?'nav-item-open':'' }}">
                                        <a href="#" class="nav-link"><i class="icon-circle-small"></i><span>Rejected Applicants</span></a>
                                        
                                        <ul class="nav nav-group-sub" data-submenu-title="Rejected Applicants">
                                            <li class="nav-item nav-item-submenu {{ (request()->is('rejected-app-date-wise/44/all')) || (request()->is('rejected-app-date-wise/44/9')) || (request()->is('rejected-app-date-wise/44/6')) || (request()->is('rejected-app-date-wise/44/3'))  ?'nav-item-open':'' }}">
                                                <a href="#" class="nav-link"><i class="icon-circle-small"></i><span>Nurse</span></a>
                                                <ul class="nav nav-group-sub" data-submenu-title="rejected">  
                                            <li class="nav-item {{ (request()->is('rejected-app-date-wise/44/3')) ? 'active_menu' : '' }}"><a href="{{ route('rejected_app_date_wise', ['category' => '44', 'month' => '3']) }}" class="nav-link"><i class="icon-dash"></i>3 Months Rejected Applicants</a></li>
                                                    <li class="nav-item {{ (request()->is('rejected-app-date-wise/44/6')) ? 'active_menu' : '' }}"><a href="{{ route('rejected_app_date_wise', ['category' => '44', 'month' => '6']) }}" class="nav-link"><i class="icon-dash"></i>6 Months Rejected Applicants</a></li>
                                                    <li class="nav-item {{ (request()->is('rejected-app-date-wise/44/9')) ? 'active_menu' : '' }}"><a href="{{ route('rejected_app_date_wise', ['category' => '44', 'month' => '9']) }}" class="nav-link"><i class="icon-dash"></i>9 Months Rejected Applicants</a></li>
                                                    <li class="nav-item {{ (request()->is('rejected-app-date-wise/44/all')) ? 'active_menu' : '' }}"><a href="{{ route('rejected_app_date_wise', ['category' => '44', 'month' => 'all']) }}" class="nav-link"><i class="icon-dash"></i>Remaining Applicants</a></li>
        
                                            </ul>
                                            </li>
                                        </ul>
                                        <ul class="nav nav-group-sub" data-submenu-title="rejected">
                                            <li class="nav-item nav-item-submenu {{ (request()->is('rejected-app-date-wise/45/all')) || (request()->is('rejected-app-date-wise/45/9')) || (request()->is('rejected-app-date-wise/45/6')) || (request()->is('rejected-app-date-wise/45/3'))  ?'nav-item-open':'' }}">
                                                <a href="#" class="nav-link"><i class="icon-circle-small"></i><span>Non Nurse</span></a>
                                                <ul class="nav nav-group-sub" data-submenu-title="rejected">  
                                                    <li class="nav-item {{ (request()->is('rejected-app-date-wise/45/3')) ? 'active_menu' : '' }}"><a href="{{ route('rejected_app_date_wise', ['category' => '45', 'month' => '3']) }}" class="nav-link"><i class="icon-dash"></i>3 Months Rejected Applicants</a></li>
                                                    <li class="nav-item {{ (request()->is('rejected-app-date-wise/45/6')) ? 'active_menu' : '' }}"><a href="{{ route('rejected_app_date_wise', ['category' => '45', 'month' => '6']) }}" class="nav-link"><i class="icon-dash"></i>6 Months Rejected Applicants</a></li>
                                                    <li class="nav-item {{ (request()->is('rejected-app-date-wise/45/9')) ? 'active_menu' : '' }}"><a href="{{ route('rejected_app_date_wise', ['category' => '45', 'month' => '9']) }}" class="nav-link"><i class="icon-dash"></i>9 Months Rejected Applicants</a></li>
                                                    <li class="nav-item {{ (request()->is('rejected-app-date-wise/45/all')) ? 'active_menu' : '' }}"><a href="{{ route('rejected_app_date_wise', ['category' => '45', 'month' => 'all']) }}" class="nav-link"><i class="icon-dash"></i>Remaining Applicants</a></li>
        
                                            </ul>
                                            </li>
                                        </ul>
                                    </li>
                                @endcan 
						<li class="nav-item {{ Route::is('last2monthsBlockedApplicants')? 'active_menu' : '' }}"><a href="{{ route('last2monthsBlockedApplicants') }}" class="nav-link"><i class="icon-circle-small"></i>Blocked Applicants</a></li>
					<?php /*	<li class="nav-item"><a href="{{ route('TempNotInterestedApplicants') }}" class="nav-link">Temporary Not Interested</a></li> 
                        
                         @can('resource_Crm-Rejected-Applicants')
                            <li class="nav-item"><a href="{{ route('crmRejectedCv') }}" class="nav-link">Crm Rejected Applicants</a></li>
                        @endcan
                        @can('resource_Crm-Request-Rejected-Applicants')
                            <li class="nav-item"><a href="{{ route('crmRequestRejectedCv') }}" class="nav-link">Crm Request Rejected Applicants</a></li>
                        @endcan
                        @can('resource_Crm-Not-Attended-Applicants')
                            <li class="nav-item"><a href="{{ route('crmNotAttendedCv') }}" class="nav-link">Crm Not Attended Applicants</a></li>
                        @endcan
                        @can('resource_Crm-Start-Date-Hold-Applicants')
                            <li class="nav-item"><a href="{{ route('crmStartDateHoldCv') }}" class="nav-link">Crm Start Date Hold Applicants</a></li>
                        @endcan 
						*/?>
                        @can('resource_Crm-Paid-Applicants')
                            <li class="nav-item {{ Route::is('crmPaidCv')? 'active_menu' : '' }}"><a href="{{ route('crmPaidCv') }}" class="nav-link"><i class="icon-circle-small"></i>CRM Paid Applicants</a></li>
                        @endcan
                        @canany(['resource_No-Nursing-Home_list','resource_No-Nursing-Home_revert-no-nursing-home'])
                            <li class="nav-item {{ Route::is('nurseHomeApplicants')? 'active_menu' : '' }}"><a href="{{ route('nurseHomeApplicants') }}" class="nav-link"><i class="icon-circle-small"></i>No Nursing Homes</a></li>
                        @endcanany
                        @canany(['resource_Potential-Callback_list','resource_Potential-Callback_revert-callback'])
                        <li class="nav-item {{ Route::is('potential-call-back-applicants')? 'active_menu' : '' }}"><a href="{{ route('potential-call-back-applicants') }}" class="nav-link"><i class="icon-circle-small"></i>Potential CallBack</a></li>
                        @endcanany
                        @can('resource_Non-Interested-Applicants')
                            <li class="nav-item {{ Route::is('nonInterestedApplicants')? 'active_menu' : '' }}"><a href="{{ route('nonInterestedApplicants') }}" class="nav-link"><i class="icon-circle-small"></i>Non-Interested Applicants</a></li>
                        @endcan
						@can('applicant_no-job')
                            <li class="nav-item {{ Route::is('get_no_job_applicants')? 'active_menu' : '' }}"><a href="{{ route('get_no_job_applicants') }}" class="nav-link"><i class="icon-circle-small"></i>No Job Applicants</a></li>
                        @endcan
                    </ul>

                </li>
                @endcanany
                <!--/main -->
                @canany(['quality_CVs_list','quality_CVs_cv-download','quality_CVs_job-detail','quality_CVs_cv-clear','quality_CVs_cv-reject','quality_CVs_manager-detail','quality_CVs-Rejected_list','quality_CVs-Rejected_job-detail','quality_CVs-Rejected_cv-download','quality_CVs-Rejected_manager-detail','quality_CVs-Rejected_revert-quality-cv','quality_CVs-Cleared_list','quality_CVs-Cleared_job-detail','quality_CVs-Cleared_cv-download','quality_CVs-Cleared_manager-detail'])
                <li class="nav-item nav-item-submenu {{Route::is('quality-sales-rejected') || Route::is('quality-sales-cleared') || Route::is('quality-sales') || Route::is('applicantsWithConfirmedInterview') || Route::is('applicantWithRejectedCV') || Route::is('applicantWithSentCv') ?'nav-item-open':'' }}">
                    <a href="#" class="nav-link"><i class="icon-medal"></i> <span>Quality</span></a>
                    <ul class="nav nav-group-sub" data-submenu-title="Quality" {{Route::is('quality-sales-rejected') || Route::is('quality-sales-cleared') || Route::is('quality-sales') || Route::is('applicantsWithConfirmedInterview') || Route::is('applicantWithRejectedCV') || Route::is('applicantWithSentCv') ?'style=display:block':''}}>
                        <li class="nav-item nav-item-submenu {{Route::is('applicantsWithConfirmedInterview') || Route::is('applicantWithRejectedCV') || Route::is('applicantWithSentCv') ?'nav-item-open':'' }}">
                            <a href="#" class="nav-link"><i class="icon-circle-small"></i>Resource</a>
                            <ul class="nav nav-group-sub" data-submenu-title="Resource" {{Route::is('applicantsWithConfirmedInterview') || Route::is('applicantWithRejectedCV') || Route::is('applicantWithSentCv') ?'style=display:block':''}}>
                                @canany(['quality_CVs_list','quality_CVs_cv-download','quality_CVs_job-detail','quality_CVs_cv-clear','quality_CVs_cv-reject','quality_CVs_manager-detail'])
                                <li class="nav-item {{ Route::is('applicantWithSentCv') ? 'active_menu':'' }}">
                                    <a href="{{ route('applicantWithSentCv') }}"
                                       class="nav-link"><i class="icon-dash"></i>
                                        CVs
                                    </a>
                                </li>
                                @endcanany
                                @canany(['quality_CVs-Rejected_list','quality_CVs-Rejected_job-detail','quality_CVs-Rejected_cv-download','quality_CVs-Rejected_manager-detail','quality_CVs-Rejected_revert-quality-cv'])
                                <li class="nav-item {{ Route::is('applicantWithRejectedCV') ? 'active_menu':'' }}">
                                    <a href="{{ route('applicantWithRejectedCV') }}"
                                       class="nav-link"><i class="icon-dash"></i>
                                        CVs Rejected
                                    </a>
                                </li>
                                @endcanany
                                @canany(['quality_CVs-Cleared_list','quality_CVs-Cleared_job-detail','quality_CVs-Cleared_cv-download','quality_CVs-Cleared_manager-detail'])
                                <li class="nav-item {{ Route::is('applicantsWithConfirmedInterview') ? 'active_menu':'' }}">
                                    <a href="{{ route('applicantsWithConfirmedInterview') }}"
                                       class="nav-link"><i class="icon-dash"></i>
                                        CVs Cleared
                                    </a>
                                </li>
                                @endcanany
                            </ul>
                        </li>
                        @canany(['quality_Sales_list','quality_Sales_sale-clear','quality_Sales_sale-reject','quality_Sales-Cleared_list','quality_Sales-Rejected_list'])
                        <li class="nav-item nav-item-submenu {{Route::is('quality-sales-rejected') || Route::is('quality-sales-cleared') ||  Route::is('quality-sales') ?'nav-item-open':'' }}">
                            <a href="#" class="nav-link"><i class="icon-circle-small"></i>Sales</a>
                            <ul class="nav nav-group-sub" {{ Route::is('quality-sales-rejected') || Route::is('quality-sales-cleared') || Route::is('quality-sales') ?'style=display:block':''}}>
                                @canany(['quality_Sales_list','quality_Sales_sale-clear','quality_Sales_sale-reject'])
                                <li class="nav-item {{ Route::is('quality-sales') ?'active_menu':''}}">
									<a href="{{ route('quality-sales') }}" class="nav-link"><i class="icon-dash"></i>Sales</a>
								</li>
                                @endcanany
                                @can('quality_Sales-Cleared_list')
                                <li class="nav-item {{ Route::is('quality-sales-cleared') ?'active_menu':''}}">
									<a href="{{ route('quality-sales-cleared') }}" class="nav-link"><i class="icon-dash"></i>Sales Cleared</a>
								</li>
                                @endcan
                                @can('quality_Sales-Rejected_list')
                                <li class="nav-item {{ Route::is('quality-sales-rejected') ?'active_menu':''}}">
									<a href="{{ route('quality-sales-rejected') }}" class="nav-link"><i class="icon-dash"></i>Sales Rejected</a>
								</li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany
                    </ul>
                </li>
                @endcanany
                @canany(['CRM_Sent-CVs_list','CRM_Sent-CVs_request','CRM_Sent-CVs_save','CRM_Sent-CVs_reject','CRM_Rejected-CV_list','CRM_Request_list','CRM_Rejected-By-Request_list','CRM_Confirmation_list','CRM_Rebook_list','CRM_Attended_list','CRM_Declined_list','CRM_Not-Attended_list','CRM_Start-Date_list','CRM_Start-Date-Hold_list','CRM_Invoice_list','CRM_Dispute_list','CRM_Paid_list'])
                <li class="nav-item {{ Route::is('index') ? 'active_menu':'' }}">
                    <a href="{{ route('index') }}" class="nav-link"><i class="icon-stats-decline2"></i> <span>CRM</span></a>
                </li>
                @endcanany
				                @canany(['Region_scotland','Region_Northern-Ireland','Region_South-East','Region_Wales','Region_North-East','Region_North-West'
,'Region_West-Midlands','Region_East-Midlands','Region_South-West','Region_South-East','Region_East-of-England','Region_Greater-London','Region_Common-Regions'])


                <li class="nav-item nav-item-submenu">
                        <a href="#" class="nav-link"><i class="icon-location3"></i> <span>Regions</span></a>
                        <ul class="nav nav-group-sub" data-submenu-title="Quality">
                          @can('Region_scotland')
                            <li class="nav-item nav-item-submenu">
                                <a href="#" class="nav-link" id="1"><i class="icon-city"></i>Scotland</a>
                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" id="1"
                                           class="nav-link">
                                            Applicants
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '1','category' => '44']) }}" id="1"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '1','category' => '45']) }}" id="1"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionAppNonNurseSpec', ['id' => '1','category' => '45']) }}" id="1"
                                                   class="nav-link">
                                                    Specialist
                                                </a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" id="1"
                                           class="nav-link">
                                            Sales
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '1','category' => '44']) }}" id="1"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '1','category' => '45']) }}" id="1"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            @endcan
                              @can('Region_Northern-Ireland')
                            <li class="nav-item nav-item-submenu">
                                <a href="#" class="nav-link" id="2"><i class="icon-city" ></i>Northern Ireland</a>
                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                    <li class="nav-item nav-item-submenu">
                                        <a href="{{ route('regionApplicants', ['id' => '2']) }}" id="2"
                                           class="nav-link">
                                            Applicants
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '2','category' => '44']) }}" id="2"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '2','category' => '45']) }}" id="2"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionAppNonNurseSpec', ['id' => '2','category' => '45']) }}" id="2"
                                                   class="nav-link">
                                                    Specialist
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" id="2"
                                           class="nav-link">
                                            Sales
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '2','category' => '44']) }}" id="2"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '2','category' => '45']) }}" id="2"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                              @endcanany
                              @can('Region_Wales')
                            <li class="nav-item nav-item-submenu">
                                <a href="#" class="nav-link" id="3"><i class="icon-city" ></i>Wales</a>
                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                    <li class="nav-item nav-item-submenu">
                                        <a href="{{ route('regionApplicants', ['id' => '3']) }}" id="3"
                                           class="nav-link">
                                            Applicants
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '3','category' => '44']) }}" id="3"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '3','category' => '45']) }}" id="3"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionAppNonNurseSpec', ['id' => '3','category' => '45']) }}" id="3"
                                                   class="nav-link">
                                                    Specialist
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" id="3"
                                           class="nav-link">
                                            Sales
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '3','category' => '44']) }}" id="3"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '3','category' => '45']) }}" id="3"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                              @endcan
                              @can('Region_North-East')
                            <li class="nav-item nav-item-submenu">
                                <a href="#" class="nav-link" id="4"><i class="icon-city" ></i>North East</a>
                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                    <li class="nav-item nav-item-submenu">
                                        <a href="{{ route('regionApplicants', ['id' => '4']) }}" id="4"
                                           class="nav-link">
                                            Applicants
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '4','category' => '44']) }}" id="4"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '4','category' => '45']) }}" id="4"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionAppNonNurseSpec', ['id' => '4','category' => '45']) }}" id="4"
                                                   class="nav-link">
                                                    Specialist
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" id="4"
                                           class="nav-link">
                                            Sales
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '4','category' => '44']) }}" id="4"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '4','category' => '45']) }}" id="4"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                              @endcan
                              @can('Region_North-West')
                            <li class="nav-item nav-item-submenu">
                                <a href="#" class="nav-link" id="5"><i class="icon-city" ></i>North West</a>
                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                    <li class="nav-item nav-item-submenu">
                                        <a href="{{ route('regionApplicants', ['id' => '5']) }}" id="5"
                                           class="nav-link">
                                            Applicants
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '5','category' => '44']) }}" id="5"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '5','category' => '45']) }}" id="5"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionAppNonNurseSpec', ['id' => '5','category' => '45']) }}" id="5"
                                                   class="nav-link">
                                                    Specialist
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" id="5"
                                           class="nav-link">
                                            Sales
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '5','category' => '44']) }}" id="5"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '5','category' => '45']) }}" id="5"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                              @endcan
                              @can('Region_West-Midlands')
                            <li class="nav-item nav-item-submenu">
                                <a href="#" class="nav-link" id="6"><i class="icon-city" ></i>West Midlands</a>
                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                    <li class="nav-item nav-item-submenu">
                                        <a href="{{ route('regionApplicants', ['id' => '6']) }}" id="6"
                                           class="nav-link">
                                            Applicants
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '6','category' => '44']) }}" id="6"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '6','category' => '45']) }}" id="6"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionAppNonNurseSpec', ['id' => '6','category' => '45']) }}" id="6"
                                                   class="nav-link">
                                                    Specialist
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" id="6"
                                           class="nav-link">
                                            Sales
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '6','category' => '44']) }}" id="6"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '6','category' => '45']) }}" id="6"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                              @endcan
                              @can('Region_East-Midlands')
                            <li class="nav-item nav-item-submenu">
                                <a href="#" class="nav-link" id="7"><i class="icon-city" ></i>East Midlands</a>
                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                    <li class="nav-item nav-item-submenu">
                                        <a href="{{ route('regionApplicants', ['id' => '7']) }}" id="7"
                                           class="nav-link">
                                            Applicants
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '7','category' => '44']) }}" id="7"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '7','category' => '45']) }}" id="7"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionAppNonNurseSpec', ['id' => '7','category' => '45']) }}" id="7"
                                                   class="nav-link">
                                                    Specialist
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" id="7"
                                           class="nav-link">
                                            Sales
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '7','category' => '44']) }}" id="7"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '7','category' => '45']) }}" id="7"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                              @endcan
                              @can('Region_South-West')
                            <li class="nav-item nav-item-submenu">
                                <a href="#" class="nav-link" id="8"><i class="icon-city" ></i>South West</a>
                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                    <li class="nav-item nav-item-submenu">
                                        <a href="{{ route('regionApplicants', ['id' => '8']) }}" id="8"
                                           class="nav-link">
                                            Applicants
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '8','category' => '44']) }}" id="8"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '8','category' => '45']) }}" id="8"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionAppNonNurseSpec', ['id' => '8','category' => '45']) }}" id="8"
                                                   class="nav-link">
                                                    Specialist
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" id="8"
                                           class="nav-link">
                                            Sales
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '8','category' => '44']) }}" id="8"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '8','category' => '45']) }}" id="8"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                              @endcan
                              @can('Region_South-East')
                            <li class="nav-item nav-item-submenu">
                                <a href="#" class="nav-link" id="9"><i class="icon-city" ></i>South East</a>
                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                    <li class="nav-item nav-item-submenu">
                                        <a href="{{ route('regionApplicants', ['id' => '9']) }}" id="9"
                                           class="nav-link">
                                            Applicants
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '9','category' => '44']) }}" id="9"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '9','category' => '45']) }}" id="9"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionAppNonNurseSpec', ['id' => '9','category' => '45']) }}" id="9"
                                                   class="nav-link">
                                                    Specialist
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" id="9"
                                           class="nav-link">
                                            Sales
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '9','category' => '44']) }}" id="9"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '9','category' => '45']) }}" id="9"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                              @endcan
                              @can('Region_East-of-England')
                            <li class="nav-item nav-item-submenu">
                                <a href="#" class="nav-link" id="10"><i class="icon-city" ></i>East of England</a>
                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                    <li class="nav-item nav-item-submenu">
                                        <a href="{{ route('regionApplicants', ['id' => '10']) }}" id="10"
                                           class="nav-link">
                                            Applicants
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '10','category' => '44']) }}" id="10"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '10','category' => '45']) }}" id="10"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionAppNonNurseSpec', ['id' => '10','category' => '45']) }}" id="10"
                                                   class="nav-link">
                                                    Specialist
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" id="10"
                                           class="nav-link">
                                            Sales
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '10','category' => '44']) }}" id="10"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '10','category' => '45']) }}" id="10"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                              @endcan
                              @can('Region_Greater-London')
                            <li class="nav-item nav-item-submenu">
                                <a href="#" class="nav-link" id="11"><i class="icon-city" ></i>Greater London</a>
                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                    <li class="nav-item nav-item-submenu">
                                        <a href="{{ route('regionApplicants', ['id' => '11']) }}" id="11"
                                           class="nav-link">
                                            Applicants
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '11','category' => '44']) }}" id="11"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '11','category' => '45']) }}" id="11"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionAppNonNurseSpec', ['id' => '11','category' => '45']) }}" id="11"
                                                   class="nav-link">
                                                    Specialist
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" id="11"
                                           class="nav-link">
                                            Sales
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '11','category' => '44']) }}" id="11"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '11','category' => '45']) }}" id="11"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                              @endcan
                              @can('Region_Common-Regions')
                            <li class="nav-item nav-item-submenu">
                                <a href="#" class="nav-link" id="12"><i class="icon-city" ></i>Common Regions</a>
                                <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                    <li class="nav-item nav-item-submenu">
                                        <a href="{{ route('regionApplicants', ['id' => '12']) }}" id="12"
                                           class="nav-link">
                                            Applicants
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '12','category' => '44']) }}" id="12"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionApp', ['id' => '12','category' => '45']) }}" id="12"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('regionAppNonNurseSpec', ['id' => '12','category' => '45']) }}" id="12"
                                                   class="nav-link">
                                                    Specialist
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item nav-item-submenu">
                                        <a href="#" id="12"
                                           class="nav-link">
                                            Sales
                                        </a>
                                        <ul class="nav nav-group-sub" data-submenu-title="Resource">
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '12','category' => '44']) }}" id="12"
                                                   class="nav-link">
                                                    Nurse
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('region.nurses.sales', ['id' => '12','category' => '45']) }}" id="12"
                                                   class="nav-link">
                                                    Non-Nurse
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                              @endcan
                        </ul>
                    </li>
                @endcanany

				@canany(['applicant_message-inbox', 'applicant_message-send'])
                <li class="nav-item nav-item-submenu {{ Route::is('send_message') || Route::is('message_inbox') ?'nav-item-open':'' }}">
                        <a href="#" class="nav-link"><i class="icon-envelop4"></i><span>User Messages</span></a>
                        <ul class="nav nav-group-sub" data-submenu-title="Administration" {{ Route::is('send_message') || Route::is('message_inbox') ?'style=display:block':''}}>
                        @canany(['applicant_message-inbox'])
                            <li class="nav-item {{ Route::is('message_inbox') ? 'active_menu':'' }}">
								<a href="{{ route('message_inbox') }}" class="nav-link"><i class="icon-circle-small"></i>Inbox Messages</a></li>
                            @endcanany
                            @canany(['applicant_message-send'])
                            <li class="nav-item {{ Route::is('send_message') ? 'active_menu':'' }}">
								<a href="{{ route('send_message') }}" class="nav-link"><i class="icon-circle-small"></i>Send Messages</a></li>
                            @endcanany
                            <!-- applicant_message-send -->
                        </ul>
                    </li>
                    @endcanany
                @canany(['ip-address_list','ip-address_create','ip-address_edit','ip-address_enable-disable','ip-address_delete','role_list','role_create','role_view','role_edit','role_delete','role_assign-role','applicant_generic-email','applicant_sent-email'])
                    <li class="nav-item nav-item-submenu {{ Route::is('ip-addresses.index') || Route::is('sent_emails') || Route::is('login_details') || Route::is('emailTemplates') || Route::is('roles.index') ?'nav-item-open':'' }}">
                        <a href="#" class="nav-link"><i class="icon-users2"></i> <span>Administration</span></a>
                        <ul class="nav nav-group-sub" data-submenu-title="Administration" {{ Route::is('ip-addresses.index') || Route::is('login_details') || Route::is('sent_emails') || Route::is('emailTemplates') || Route::is('roles.index') ?'style=display:block':''}}>
                            @canany(['ip-address_list','ip-address_create','ip-address_edit','ip-address_enable-disable','ip-address_delete'])
                            <li class="nav-item {{ Route::is('ip-addresses.index') ? 'active_menu':'' }}"><a href="{{ route('ip-addresses.index') }}" class="nav-link"><i class="icon-circle-small"></i>IP Addresses</a></li>
                            @endcanany
                            @canany(['role_list','role_create','role_view','role_edit','role_delete','role_assign-role'])
                            <li class="nav-item {{ Route::is('roles.index') ? 'active_menu':'' }}"><a class="nav-link" href="{{ route('roles.index') }}"><i class="icon-circle-small"></i>Roles & Permissions</a></li>
                            @endcanany
							@can('applicant_generic-email')
                            <li class="nav-item {{ Route::is('emailTemplates') ? 'active_menu':'' }}"><a href="{{ route('emailTemplates') }}" class="nav-link"><i class="icon-circle-small"></i>Email Templates</a></li>
                            @endcan
							@can('applicant_sent-email')
                            <li class="nav-item {{ Route::is('sent_emails') ? 'active_menu':'' }}"><a href="{{ route('sent_emails') }}" class="nav-link"><i class="icon-circle-small"></i>Sent Emails</a></li>
                            @endcan
							@if (Auth::user()->hasRole('super_admin'))
                            <li class="nav-item {{ Route::is('login_details') ? 'active_menu':'' }}"><a class="nav-link" href="{{ route('login_details') }}"><i class="icon-circle-small"></i>Login Info</a></li>
                            @endif
                        </ul>
                    </li>
                @endcanany
				<li class="nav-item  {{Route::is('specialist_title.edit') || Route::is('specialist_titles.index') || Route::is('specialist_titles.create') ? 'active_menu':''}}">
                    <a class="nav-link" href="{{ route('specialist_titles.index') }}"><i class="icon-briefcase"></i><span>Specialist Title</span></a>
                </li>
                {{-- @can('postcode-finder_search')--}}
                <li class="nav-item {{ Route::is('postcodeFinder') || Route::is('postcodeFinderResults') ? 'active_menu':''}}">
                    <a href="{{ route('postcodeFinder') }}" class="nav-link"><i class="icon-location4"></i> <span>Postcode Finder</span></a>
                </li>
                {{-- @endcan--}}
                <li class="nav-item {{ Route::is('followUP.sheet') || Route::is('followUP.sheet') ? 'active_menu':''}}">
                    <a href="{{ route('followUP.sheet') }}" class="nav-link"><i class="icon-file-text"></i> <span>Follow-Up Sheet</span></a>
                </li>
            </ul>
        </div>
        <!-- /main navigation -->

    </div>
    <!-- /sidebar content -->

</div>
<!-- /main sidebar -->
