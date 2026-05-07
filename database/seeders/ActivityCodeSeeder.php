<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminAccount;
use Illuminate\Support\Facades\DB;

class ActivityCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("
        INSERT INTO `activity_codes` (`id`, `activityCode`, `activity`, `category`, `severity`, `action`, `adminLevel`) VALUES
(1, '00001', 'Logged in', 'login', 'info', 'login', 1),
(2, '00002', 'Added a new stockholder', 'account', 'info', 'create', 1),

(5, '00005', 'Requested OTP', 'login', 'info', 'login', 1),
(6, '00006', 'Account number or email is incorrect', 'login', 'warning', 'login', 1),
(7, '00000', 'SYSTEM', 'system', 'warning', 'error', 0),
(8, '00007', 'Invalid OTP', 'login', 'warning', 'login', 1),
(9, '00008', 'Logged out', 'logout', 'info', 'logout', 1),
(23, '00022', 'View activity logs', 'activity', 'info', 'view', 1),
(25, '00024', 'Viewed the document', 'Document', 'info', 'view', 1),
(26, '00025', 'Added a new candidate', 'candidate', 'info', 'create', 1),
(27, '00026', 'Edited a candidate', 'candidate', 'info', 'update', 1),
(28, '00027', 'Added a non member', 'Non-member', 'info', 'create', 1),
(29, '00028', 'Edited non member account', 'Non-member', 'info', 'update', 1),
(30, '00029', 'Edited a stockholder', 'stockholder', 'warning', 'update', 1),
(31, '00030', 'Assigned BOD proxy', 'BOD Proxy', 'info', 'assign', 1),
(32, '00031', 'Cancelled BOD Proxy', 'BOD Proxy', 'info', 'cancel', 1),

(35, '00034', 'Invalid email or password', 'login', 'warning', 'login', 1),
(36, '00035', 'Changed password', 'password', 'info', 'change pas', 1),
(39, '00038', 'Imported Stockholder', 'stockholder', 'info', 'import', 1),
(40, '00039', 'Audited BOD Proxy', 'BOD Proxy', 'info', 'audit', 1),
(41, '00040', 'Audited BOD Proxy (Revoked)', 'BOD Proxy', 'info', 'audit', 1),
(42, '00041', 'Added admin account', 'admin', 'info', 'create', 1),
(43, '00042', 'Edited admin account', 'admin', 'info', 'update', 1),
(44, '00043', 'Replaced OTP', 'login', 'warning', 'overwite', 1),
(45, '00044', 'Voted in person', 'vote', 'info', 'vote', 2),
(46, '00045', 'Voted by proxy', 'vote', 'info', 'vote', 2),
(47, '00046', 'System Log', 'log', 'info', 'log', 3),
(48, '00047', 'Generated Ballot (Stockholder Online)', 'Stokcholder Online', 'info', 'generate', 2),
(49, '00048', 'Generated Ballot (Proxy Voting)', 'Proxy Voting', 'info', 'generate', 2),
(50, '00049', 'Updated election date (in person)', 'Stockholder Online', 'info', 'update', 1),
(51, '00050', 'Updated election date (by proxy)', 'Proxy', 'info', 'update', 1),
(52, '00051', 'The Stockholder Online Voting date has been successfully removed', 'Stockholder Online', 'warning', 'update', 1),
(53, '00052', 'Removed election date (BY PROXY)', 'Proxy', 'warning', 'update', 1),
(54, '00053', 'Updated submission date (PROXY)', 'Proxy', 'info', 'update', 1),
(55, '00054', 'Removed submission date (PROXY)', 'Proxy', 'info', 'update', 1),
(56, '00055', 'Viewed ballot page', 'ballot', 'warning', 'view', 1),
(57, '00056', 'Viewed attendance page', 'ballot', 'warning', 'view', 1),
(58, '00057', 'Viewed election result page', 'ballot', 'warning', 'view', 3),
(59, '00058', 'Exported election result', 'ballot', 'warning', 'export', 3),
(61, '00060', 'Uploaded a document', 'document', 'info', 'upload', 1),
(62, '00061', 'Archived a document', 'document', 'info', 'archive', 1),


(70, '00069', 'Added amendment', 'Amendment', 'info', 'create', 1),
(71, '00070', 'Edited an amendment', 'Amendment', 'info', 'update', 1),
(72, '00071', 'Stockholder Online', 'summary', 'info', 'summary', 3),
(73, '00072', 'Proxy Voting', 'summary', 'info', 'summary', 3),
(74, '00073', 'Viewed ballot form', 'ballot', 'warning', 'view', 3),
(75, '00074', 'Vewed ballot confirmation summary', 'summary', 'warning', 'view', 3),
(76, '00075', 'Voided a vote', 'ballot', 'warning', 'void', 3),
(77, '00076', 'No posted date for Stockholder Online or ended.', 'Stockholder Online', 'info', 'view', 3),
(78, '00077', 'No posted date for Stockholder Online or ended.', 'Stockholder Online', 'info', 'summary', 3),
(79, '00078', 'No posted date for Stockholder Online or ended.', 'Stockholder Online', 'info', 'cast', 3),
(80, '00079', 'No posted date for Stockholder Online or ended.', 'Proxy Voting', 'info', 'view', 3),
(81, '00080', 'No posted date for Stockholder Online or ended.', 'Proxy Voting', 'info', 'summary', 3),
(82, '00081', 'No posted date for Stockholder Online or ended.', 'Proxy Voting', 'info', 'cast', 3),
(83, '00082', 'Available vote for BOD changed.', 'Stockholder Online', 'info', 'summary', 3),
(84, '00083', 'Available vote for BOD changed.', 'Stockholder Online', 'info', 'cast', 3),
(85, '00084', 'Available vote for BOD changed.', 'Proxy Voting', 'info', 'summary', 3),
(86, '00085', 'Available vote for BOD changed.', 'Proxy Voting', 'info', 'cast', 3),
(87, '00086', 'Available vote for Amendment changed.', 'Stockholder Online', 'info', 'summary', 3),
(88, '00087', 'Available vote for Amendment changed.', 'Stockholder Online', 'info', 'cast', 3),
(89, '00088', 'Available vote for Amendment changed.', 'Proxy Voting', 'info', 'summary', 3),
(90, '00089', 'Available vote for Amendment changed.', 'Proxy Voting', 'info', 'cast', 3),
(91, '00090', 'Warning arning message', 'Stockholder Online', 'info', 'ballot', 3),
(92, '00091', 'Warning message', 'Proxy Voting', 'info', 'ballot', 3),
(93, '00092', 'BOD warning message', 'Stockholder Online', 'critical', 'cast', 3),
(94, '00093', 'BOD warning message', 'Proxy Voting', 'critical', 'cast', 3),
(95, '00094', 'Stockholder Online ballot has been successfully submitted', 'Stockholder Online', 'info', 'cast', 3),
(96, '00095', 'Proxy voting ballot has been successfully submitted', 'Proxy Voting', 'critical', 'cast', 3),
(97, '00096', 'Added agenda', 'agenda', 'info', 'create', 1),
(98, '00097', 'Edited an agenda', 'agenda', 'info', 'update', 1),
(99, '00098', 'Added a new stock', 'Stock', 'info', 'create', 1),
(100, '00099', 'Assigned amendment proxy', 'Amendment', 'info', 'assign', 1),
(101, '00100', 'Cancelled amendment Proxy', 'Amendent Proxy', 'info', 'cancel', 1),
(102, '00101', 'Audited Amendment Proxy', 'Amendment Proxy', 'info', 'audit', 1),
(103, '00102', 'Audited BOD Proxy(Revoked)', 'Amendment Proxy', 'info', 'audit', 1),
(104, '00103', 'Added a stock', 'account', 'info', 'create', 1),
(105, '00104', 'Register', 'register', 'info', 'register', 3),
(106, '00105', 'Exported Stockholder List with Quorum', 'stockholder', 'info', 'export', 1),
(107, '00106', 'Exported BOD Proxies', 'stockholder', 'info', 'export', 1),
(108, '00107', 'Exported Amendment Proxies', 'stockholder', 'info', 'export', 1),
(109, '00108', 'Exported Attendance', 'stockholder', 'info', 'export', 1),
(110, '00109', 'Viewed the amendment proxy list page', 'Amendment Proxy', 'info', 'view', 1),
(111, '00110', 'Viewed the amendment summary list page', 'Amendment Proxy', 'info', 'view', 1),
(112, '00111', 'Viewed the BOD proxy list page', 'BOD Proxy', 'info', 'view', 1),
(113, '00112', 'Viewed the BOD proxy summary page', 'BOD Proxy', 'info', 'view', 1),
(114, '00113', 'Updated role permissions', 'Role', 'info', 'update', 1),
(115, '00114', 'Viewed BOD proxy masterlist', 'BOD Proxy', 'info', 'view', 1),
(116, '00115', 'Viewed amendment proxy masterlist', 'Amendment Proxy', 'info', 'view', 1),
(117, '00116', 'Updated votes per share setting', 'Settings', 'info', 'update', 1),
(118, '00117', 'Enabled amendment module', 'Settings', 'info', 'update', 1),
(119, '00118', 'Disabled amendment module', 'Settings', 'warning', 'update', 1),
(120, '00119', 'Created announcement', 'Announcement', 'info', 'create', 1),
(121, '00120', 'Updated announcement', 'Announcement', 'info', 'update', 1),
(122, '00121', 'Deleted announcement', 'Announcement', 'warning', 'delete', 1),
(123, '00122', 'Enabled OTP login', 'Announcement', 'info', 'enable', 1),
(124, '00123', 'Disabled OTP login', 'Announcement', 'warning', 'disable', 1),
(125, '00124', 'Enabled email vote confirmation', 'Vote Confirmation', 'info', 'enable', 1),
(126, '00125', 'Disabled email vote confirmation', 'Vote Confirmation', 'warning', 'disable', 1),
(127, '00126', 'Email is not registered or inactive', 'OTP', 'info', 'login', 1),
(128, '00127', 'Exported Board of Director Proxy Masterlist', 'board of directors', 'info', 'export', 1),
(129, '00128', 'Exported Amendment Proxy Masterlist', 'Amendment Proxy', 'info', 'export', 1),
(130, '00129', 'Exported Active Board of Director Proxy', 'board of directors', 'info', 'export', 1),
(131, '00130', 'Exported Active Amendment Proxy', 'Amendment Proxy', 'info', 'export', 1),
(132, '00131', 'Reset admin password', 'admin', 'info', 'reset', 1),
(133, '00132', 'View voting page', 'Voting', 'info', 'view', 1),
(134, '00133', 'Updated terms and conditions for Stockholder Online Voting', 'Settings', 'info', 'update', 1),
(135, '00134', 'Updated terms and conditions for Proxy Voting', 'Settings', 'info', 'update', 1),
(136, '00135', 'Viewed the proxy inquiry page', 'Inquiry', 'info', 'view', 1),
(137, '00136', 'Viewed the available proxy votes in the inquiry page', 'Inquiry', 'info', 'view', 1),
(138, '00137', 'Printed the valid available proxies per assignee', 'report', 'info', 'print', 1),
(139, '00138', 'Viewed developer stock page', 'report', 'info', 'view', 1),
(140, '00139', 'Viewed BOD proxy assignor on the summary page', 'report', 'info', 'view', 1),
(141, '00140', 'Printed Attendance Summary', 'attendance', 'info', 'print', 1),
(142, '00141', 'Enabled Board of Director module', 'Settings', 'info', 'update', 1),
(143, '00142', 'Disabled Board of Director module', 'Settings', 'warning', 'update', 1),
(144, '00143', 'Enabled amendment restriction', 'Settings', 'info', 'update', 1),
(145, '00144', 'Disabled amendment restriction', 'Settings', 'warning', 'update', 1) 
        ");
    }
}
