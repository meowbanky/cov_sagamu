</main>
</div> <!-- .flex -->

<!-- Footer -->

<script>
$(function() {
    // Hamburger opens sidebar
    $('#menu-btn').on('click', function(e) {
        e.stopPropagation();
        $('#sidebar').removeClass('-translate-x-full').addClass('show');
        // Add overlay background
        if ($('#sidebar-overlay').length === 0) {
            $('body').append(
                '<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden"></div>'
            );
        }
        $('body').css('overflow', 'hidden'); // Prevent body scroll when sidebar is open
    });
    // Close button closes sidebar
    $('#close-sidebar').on('click', function(e) {
        e.stopPropagation();
        $('#sidebar').addClass('-translate-x-full').removeClass('show');
        $('#sidebar-overlay').remove();
        $('body').css('overflow', ''); // Restore body scroll
    });
    // Click outside sidebar closes it (on mobile)
    $(document).on('click', '#sidebar-overlay', function() {
        $('#sidebar').addClass('-translate-x-full').removeClass('show');
        $(this).remove();
        $('body').css('overflow', ''); // Restore body scroll
    });
    // Close sidebar when clicking a link (mobile only)
    $('#sidebar a').on('click', function() {
        if ($(window).width() < 768) {
            $('#sidebar').addClass('-translate-x-full').removeClass('show');
            $('#sidebar-overlay').remove();
            $('body').css('overflow', '');
        }
    });
    // Ensure sidebar shows on desktop resize
    $(window).on('resize', function() {
        if ($(window).width() >= 768) {
            $('#sidebar').removeClass('-translate-x-full').removeClass('show');
            $('#sidebar-overlay').remove();
            $('body').css('overflow', '');
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<footer class="w-full bg-white text-gray-500 text-center py-4 shadow-inner mt-10">
    &copy; <?= date("Y") ?> BankSoft Solutions. All rights reserved.
</footer>
</body>

</html>