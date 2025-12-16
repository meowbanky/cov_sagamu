<?php require_once('header.php');

// Fetch all members ONCE (in real world, you can do SQL pagination/search)
$stmt = $cov->prepare("
    SELECT
        p.sfxname, p.memberid,
        CONCAT(IFNULL(p.Lname,''),', ',IFNULL(p.Fname,''),' ',IFNULL(p.Mname,'')) AS namee,
        IFNULL(p.gender,'Male') AS gender,
        p.DOB, p.Address, p.Address2, p.City, p.State, p.MobilePhone, p.passport, p.dept,
        p.Status,
        n.NOkName, n.NOKRelationship, n.NOKPhone, n.NOKAddress
    FROM tbl_personalinfo p
    LEFT JOIN tbl_nok n ON n.memberid = p.memberid
    ORDER BY p.memberid
");
$stmt->execute();
$result = $stmt->get_result();
$members = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<main class="flex-1 py-8 px-2 md:px-10 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold text-blue-900 mb-6 text-center">Members List</h1>

        <!-- Search Box -->
        <div class="flex justify-center mb-6">
            <input id="searchBox" type="text" placeholder="Search by name, ID, or phone..."
                class="w-full max-w-lg px-4 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-base" />
        </div>

        <!-- Member List -->
        <div id="memberGrid" class="grid gap-6 grid-cols-1 sm:grid-cols-2 md:grid-cols-3"></div>

        <!-- Pagination -->
        <div class="flex justify-center mt-8" id="pagination"></div>
    </div>
</main>

<script>
const MEMBERS = <?= json_encode($members) ?>;
const PAGE_SIZE = 50;
let page = 1;
let filtered = MEMBERS;

function svgAvatar(gender, name) {
    // Simple modern SVG avatar; customize as you wish!
    let initials = (name || '').split(/[ ,]+/).filter(Boolean).map(n => n[0]).join('').substring(0, 2).toUpperCase();
    let color = gender === "Female" ? "#dbeafe" : "#fef9c3";
    let face = gender === "Female" ?
        `<ellipse cx="50" cy="50" rx="38" ry="36" fill="#fbcfe8"/><ellipse cx="50" cy="38" rx="15" ry="8" fill="#f472b6"/><ellipse cx="50" cy="63" rx="18" ry="16" fill="#f9fafb"/><ellipse cx="50" cy="55" rx="15" ry="13" fill="#f3f4f6"/><ellipse cx="40" cy="45" rx="3" ry="3.2" fill="#fbbf24"/><ellipse cx="60" cy="45" rx="3" ry="3.2" fill="#fbbf24"/>` :
        `<circle cx="50" cy="50" r="38" fill="#bae6fd"/><ellipse cx="50" cy="66" rx="15" ry="9" fill="#f3f4f6"/><circle cx="40" cy="45" r="4" fill="#fbbf24"/><circle cx="60" cy="45" r="4" fill="#fbbf24"/>`;
    return `
    <svg width="64" height="64" viewBox="0 0 100 100">
      <rect width="100" height="100" rx="50" fill="${color}" />
      ${face}
      <text x="50%" y="87%" text-anchor="middle" font-size="22" fill="#334155" font-family="sans-serif" font-weight="bold">${initials}</text>
    </svg>
  `;
}

function renderPage() {
    // Slice the members
    let start = (page - 1) * PAGE_SIZE,
        end = start + PAGE_SIZE;
    let pageMembers = filtered.slice(start, end);

    let html = pageMembers.map(m => `
    <div class="bg-white shadow-md rounded-xl p-6 flex flex-col items-center transition hover:scale-105">
      ${
        m.passport
        ? `<img src="${m.passport}" alt="passport" class="w-20 h-20 rounded-full object-cover border mb-3 bg-gray-50">`
        : `<div class="w-20 h-20 rounded-full mb-3 border flex items-center justify-center bg-gray-100">${svgAvatar(m.gender, m.namee)}</div>`
      }
      <div class="text-blue-900 font-extrabold text-lg mb-1">${m.namee}</div>
      <div class="text-gray-500 text-sm mb-2">ID: <span class="font-medium">${m.memberid}</span></div>
      <div class="flex gap-2 items-center mb-2 flex-wrap justify-center">
        <span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">${m.gender}</span>
        ${m.dept ? `<span class="inline-block px-2 py-1 bg-green-50 text-green-700 rounded text-xs">${m.dept}</span>` : ""}
        <span class="inline-block px-2 py-1 rounded text-xs status-badge-${m.memberid} ${(m.Status === 'Active') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${m.Status || 'In-Active'}</span>
      </div>
      <div class="text-gray-600 text-xs mb-2"><i class="fa fa-phone-alt mr-1"></i>${m.MobilePhone || ''}</div>
      <div class="w-full mt-2 text-xs">
        <div class="mb-1"><span class="text-gray-500 font-semibold">Next of Kin:</span> ${m.NOkName || ''}</div>
        <div class="mb-1"><span class="text-gray-500 font-semibold">NOK Phone:</span> ${m.NOKPhone || ''}</div>
        ${m.NOKRelationship ? `<div><span class="text-gray-500 font-semibold">NOK Relationship:</span> ${m.NOKRelationship}</div>` : ""}
      </div>
      <div class="flex gap-2 mt-4">
        <button data-member-id="${m.memberid}" 
                data-current-status="${m.Status || 'In-Active'}"
                class="toggle-status-btn inline-block px-3 py-1 rounded shadow text-xs font-semibold transition status-btn-${m.memberid} ${(m.Status === 'Active') ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-green-500 hover:bg-green-600 text-white'}"
                title="${(m.Status === 'Active') ? 'Deactivate Member' : 'Activate Member'}">
          <i class="fa ${(m.Status === 'Active') ? 'fa-ban' : 'fa-check-circle'} mr-1"></i>
          ${(m.Status === 'Active') ? 'Deactivate' : 'Activate'}
        </button>
        <a href="edit_member.php?id=${m.memberid}" class="inline-block bg-yellow-400 hover:bg-yellow-500 text-white font-semibold px-4 py-1 rounded shadow text-xs transition"><i class='fa fa-edit mr-1'></i>Edit</a>
      </div>
    </div>
  `).join('');

    document.getElementById('memberGrid').innerHTML = html ||
        `<div class="col-span-full text-center text-gray-400 py-16">No members found.</div>`;

    // Pagination controls
    let totalPages = Math.ceil(filtered.length / PAGE_SIZE);
    let pagHTML = '';
    if (totalPages > 1) {
        pagHTML +=
            `<button class="px-3 py-1 mx-1 rounded ${page==1?'bg-blue-100 text-blue-400':'bg-blue-600 text-white hover:bg-blue-700'}" ${page==1?'disabled':''} onclick="gotoPage(${page-1})">&laquo; Prev</button>`;
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || Math.abs(i - page) <= 1) { // show first, last, and nearby
                pagHTML +=
                    `<button class="px-3 py-1 mx-1 rounded ${i==page?'bg-blue-700 text-white':'bg-blue-100 text-blue-700 hover:bg-blue-600 hover:text-white'}" onclick="gotoPage(${i})">${i}</button>`;
            } else if (i === page - 2 || i === page + 2) {
                pagHTML += '<span class="px-2">...</span>';
            }
        }
        pagHTML +=
            `<button class="px-3 py-1 mx-1 rounded ${page==totalPages?'bg-blue-100 text-blue-400':'bg-blue-600 text-white hover:bg-blue-700'}" ${page==totalPages?'disabled':''} onclick="gotoPage(${page+1})">Next &raquo;</button>`;
    }
    document.getElementById('pagination').innerHTML = pagHTML;
}

function gotoPage(p) {
    page = p;
    renderPage();
}

document.getElementById('searchBox').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    filtered = MEMBERS.filter(m =>
        (m.namee && m.namee.toLowerCase().includes(term)) ||
        (m.memberid && String(m.memberid).toLowerCase().includes(term)) ||
        (m.MobilePhone && m.MobilePhone.toLowerCase().includes(term))
    );
    page = 1;
    renderPage();
});

// Toggle member status
function toggleMemberStatus(memberid, currentStatus) {
    const newStatus = currentStatus === 'Active' ? 'In-Active' : 'Active';
    const actionText = newStatus === 'Active' ? 'activate' : 'deactivate';

    Swal.fire({
        title: 'Change Member Status?',
        html: `Are you sure you want to <strong>${actionText}</strong> this member?<br><small class="text-gray-500">Status will change from "${currentStatus}" to "${newStatus}"</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: newStatus === 'Active' ? '#16a34a' : '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Yes, ${actionText.charAt(0).toUpperCase() + actionText.slice(1)}`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Updating status...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // AJAX request
            $.ajax({
                url: 'change_member_status.php',
                type: 'POST',
                data: {
                    memberid: memberid
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update the member data in MEMBERS array
                        const memberIndex = MEMBERS.findIndex(m => m.memberid == memberid);
                        if (memberIndex !== -1) {
                            MEMBERS[memberIndex].Status = response.newStatus;
                        }

                        // Also update in filtered array if the member is in current filter
                        const filteredIndex = filtered.findIndex(m => m.memberid == memberid);
                        if (filteredIndex !== -1) {
                            filtered[filteredIndex].Status = response.newStatus;
                        }

                        // Re-render the page to reflect changes
                        renderPage();

                        Swal.fire({
                            icon: 'success',
                            title: 'Status Updated!',
                            text: `Member status changed to "${response.newStatus}"`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: response.error || 'Failed to update member status'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while updating the status. Please try again.'
                    });
                }
            });
        }
    });
}

// Attach event listeners to toggle buttons (event delegation)
document.addEventListener('click', function(e) {
    if (e.target.closest('.toggle-status-btn')) {
        const btn = e.target.closest('.toggle-status-btn');
        const memberid = btn.getAttribute('data-member-id');
        const currentStatus = btn.getAttribute('data-current-status');
        toggleMemberStatus(memberid, currentStatus);
    }
});

// Init
renderPage();
</script>

<?php require_once('footer.php'); ?>