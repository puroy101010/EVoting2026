   <div class="corporate-right-modal-content" id="corporateRightModalContent" style="display: none;">
       <!-- Form Controls Section -->
       <div class="corporate-form-section">
           <h6 class="corporate-section-title">
               <i class="fas fa-user-cog mr-2"></i>
               User Configuration
           </h6>

           <div class="corporate-form-group">
               <label class="corporate-label">Full Name</label>
               <input type="text" class="corporate-input" placeholder="Enter full name" value="John Doe">
           </div>

           <div class="corporate-form-group">
               <label class="corporate-label">Email Address</label>
               <input type="email" class="corporate-input" placeholder="Enter email" value="john.doe@company.com">
           </div>

           <div class="corporate-form-group">
               <label class="corporate-label">User Role</label>
               <select class="corporate-select">
                   <option>Administrator</option>
                   <option>Manager</option>
                   <option>User</option>
                   <option>Guest</option>
               </select>
           </div>

           <div class="corporate-form-group">
               <label class="corporate-label">Status</label>
               <div class="corporate-toggle-group">
                   <label class="corporate-toggle">
                       <input type="checkbox" checked>
                       <span class="corporate-toggle-slider"></span>
                   </label>
                   <span class="ml-2">Active</span>
               </div>
           </div>

           <div class="corporate-form-group">
               <label class="corporate-label">Description</label>
               <textarea class="corporate-textarea" rows="3" placeholder="Enter description">User has full administrative privileges</textarea>
           </div>
       </div>

       <!-- Permissions Table Section -->
       <div class="corporate-form-section">
           <h6 class="corporate-section-title">
               <i class="fas fa-shield-alt mr-2"></i>
               Permissions Matrix
           </h6>

           <div class="corporate-permissions-table">
               <table class="table corporate-mini-table">
                   <thead>
                       <tr>
                           <th>Module</th>
                           <th class="text-center">Read</th>
                           <th class="text-center">Write</th>
                           <th class="text-center">Delete</th>
                       </tr>
                   </thead>
                   <tbody>
                       <tr>
                           <td class="font-weight-bold">Users</td>
                           <td class="text-center">
                               <label class="corporate-checkbox">
                                   <input type="checkbox" checked>
                                   <span class="corporate-checkmark"></span>
                               </label>
                           </td>
                           <td class="text-center">
                               <label class="corporate-checkbox">
                                   <input type="checkbox" checked>
                                   <span class="corporate-checkmark"></span>
                               </label>
                           </td>
                           <td class="text-center">
                               <label class="corporate-checkbox">
                                   <input type="checkbox">
                                   <span class="corporate-checkmark"></span>
                               </label>
                           </td>
                       </tr>
                       <tr>
                           <td class="font-weight-bold">Roles</td>
                           <td class="text-center">
                               <label class="corporate-checkbox">
                                   <input type="checkbox" checked>
                                   <span class="corporate-checkmark"></span>
                               </label>
                           </td>
                           <td class="text-center">
                               <label class="corporate-checkbox">
                                   <input type="checkbox" checked>
                                   <span class="corporate-checkmark"></span>
                               </label>
                           </td>
                           <td class="text-center">
                               <label class="corporate-checkbox">
                                   <input type="checkbox" checked>
                                   <span class="corporate-checkmark"></span>
                               </label>
                           </td>
                       </tr>
                       <tr>
                           <td class="font-weight-bold">Reports</td>
                           <td class="text-center">
                               <label class="corporate-checkbox">
                                   <input type="checkbox" checked>
                                   <span class="corporate-checkmark"></span>
                               </label>
                           </td>
                           <td class="text-center">
                               <label class="corporate-checkbox">
                                   <input type="checkbox">
                                   <span class="corporate-checkmark"></span>
                               </label>
                           </td>
                           <td class="text-center">
                               <label class="corporate-checkbox">
                                   <input type="checkbox">
                                   <span class="corporate-checkmark"></span>
                               </label>
                           </td>
                       </tr>
                       <tr>
                           <td class="font-weight-bold">Settings</td>
                           <td class="text-center">
                               <label class="corporate-checkbox">
                                   <input type="checkbox" checked>
                                   <span class="corporate-checkmark"></span>
                               </label>
                           </td>
                           <td class="text-center">
                               <label class="corporate-checkbox">
                                   <input type="checkbox" checked>
                                   <span class="corporate-checkmark"></span>
                               </label>
                           </td>
                           <td class="text-center">
                               <label class="corporate-checkbox">
                                   <input type="checkbox">
                                   <span class="corporate-checkmark"></span>
                               </label>
                           </td>
                       </tr>
                   </tbody>
               </table>
           </div>
       </div>

       <!-- Statistics Cards -->
       <div class="corporate-form-section">
           <h6 class="corporate-section-title">
               <i class="fas fa-chart-bar mr-2"></i>
               Quick Stats
           </h6>

           <div class="row">
               <div class="col-6">
                   <div class="corporate-stat-card">
                       <div class="corporate-stat-icon">
                           <i class="fas fa-users"></i>
                       </div>
                       <div class="corporate-stat-info">
                           <div class="corporate-stat-number">24</div>
                           <div class="corporate-stat-label">Active Users</div>
                       </div>
                   </div>
               </div>
               <div class="col-6">
                   <div class="corporate-stat-card">
                       <div class="corporate-stat-icon">
                           <i class="fas fa-user-shield"></i>
                       </div>
                       <div class="corporate-stat-info">
                           <div class="corporate-stat-number">8</div>
                           <div class="corporate-stat-label">Admin Roles</div>
                       </div>
                   </div>
               </div>
           </div>
       </div>

       <!-- Recent Activity Section -->
       <div class="corporate-form-section">
           <h6 class="corporate-section-title">
               <i class="fas fa-history mr-2"></i>
               Recent Activity
           </h6>

           <div class="corporate-activity-list">
               <div class="corporate-activity-item">
                   <div class="corporate-activity-icon">
                       <i class="fas fa-user-plus text-success"></i>
                   </div>
                   <div class="corporate-activity-content">
                       <div class="corporate-activity-title">New user created</div>
                       <div class="corporate-activity-time">2 hours ago</div>
                   </div>
               </div>
               <div class="corporate-activity-item">
                   <div class="corporate-activity-icon">
                       <i class="fas fa-edit text-warning"></i>
                   </div>
                   <div class="corporate-activity-content">
                       <div class="corporate-activity-title">Role permissions updated</div>
                       <div class="corporate-activity-time">5 hours ago</div>
                   </div>
               </div>
               <div class="corporate-activity-item">
                   <div class="corporate-activity-icon">
                       <i class="fas fa-trash text-danger"></i>
                   </div>
                   <div class="corporate-activity-content">
                       <div class="corporate-activity-title">User account deleted</div>
                       <div class="corporate-activity-time">1 day ago</div>
                   </div>
               </div>
           </div>
       </div>
   </div>