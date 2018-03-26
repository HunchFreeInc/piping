$(document).foundation()
$(function() {
  $(window).on("scroll", function() {
      if($(window).scrollTop() > 50) {
          $(".background").addClass("active");
      } else {
         $(".background").removeClass("active");
      }
    });
  });