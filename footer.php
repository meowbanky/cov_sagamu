</main>
</div> <!-- .flex -->

<!-- Footer -->

<script>
$(function() {
    // Hamburger opens sidebar
    $('#menu-btn').on('click', function(e) {
        $('#sidebar').removeClass('-translate-x-full');
        // Optional: add overlay background
        if ($('#sidebar-overlay').length === 0) {
            $('body').append('<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-40 z-30 md:hidden"></div>');
        }
    });
    // Close button closes sidebar
    $('#close-sidebar').on('click', function() {
        $('#sidebar').addClass('-translate-x-full');
        $('#sidebar-overlay').remove();
    });
    // Click outside sidebar closes it (on mobile)
    $(document).on('click', '#sidebar-overlay', function() {
        $('#sidebar').addClass('-translate-x-full');
        $(this).remove();
    });
    // Ensure sidebar shows on desktop resize
    $(window).on('resize', function() {
        if ($(window).width() >= 768) {
            $('#sidebar').removeClass('-translate-x-full');
            $('#sidebar-overlay').remove();
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
