let cnt = 1;

$('.menu').click(function () {
  $(this).toggleClass('active');
  $('#header-item').toggleClass('panelactive');
});

$('#header-item a').click(function () {
  $('.menu').removeClass('active');
  $('#header-item').removeClass('panelactive');
});

$('#menu-btn').click(function () {
  if (cnt % 2) {
    $('#modal').show();
  } else {
    $('#modal').hide();
  }
  cnt++;
});

$('#modal').click(function () {
  $('#modal').hide();
  $('.menu').removeClass('active');
  $('#header-item').removeClass('panelactive');

  cnt++;
});