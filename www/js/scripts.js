$(document).ready(function () {
    if (localStorage.getItem('flashMessage')) {
        let flashMessage = localStorage.getItem('flashMessage');

        document.getElementById('flash-container').innerHTML = '<div class="alert alert-success mx-auto" role="alert"   >' + flashMessage + '</div>';

        localStorage.removeItem('flashMessage');
    }

    $('.delete').on('click', function () {
        let confirmMessage = $(this).data('message');

        if (confirm(confirmMessage)) {
            let url = $(this).attr('href');

            $.ajax({
                url: url,
                type: 'POST',
                success: function (data) {
                    if (data.redirect) {
                        localStorage.setItem('flashMessage', data.flashMessage);

                        window.location.href = data.redirect;
                    }
                }
            });
        }
    });
});
