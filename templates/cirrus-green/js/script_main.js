jQuery(document).ready(function ($) {
  var topo = $(window).scrollTop();

  if (topo >= 90 || !home) {
    $("#header_wrap").css("background-color", "#FFF");
    if ($("#logo").hasClass("white")) {
      $("#logo").removeClass("white");
    }
    $(
      "#topmenu a, #topmenu li.active a, #topmenu li.active a:hover, #topmenu span, #topmenu li.active span, #topmenu li.active span:hover"
    ).css("color", "#504f51");
    $("#topmenu li ul").css("background-color", "#FFF");
    if (!$("#topmenu a").hasClass("border-grey")) {
      $("#topmenu a").addClass("border-grey");
    }
    if (!$("ul.contact-social li a").hasClass("color-blue")) {
      $("ul.contact-social li a").addClass("color-blue");
    }
    if (!$(".btn.btn-cotacao").hasClass("border-blue")) {
      $(".btn.btn-cotacao").addClass("border-blue");
      $(".btn.btn-cotacao").addClass("color-blue");
    }
    console.log('321321');
    $("#header_wrap").next().css("margin-top", $("#header").height());
  }

  $(window)
    .on("resize", function () {
      $(window).scroll(function () {
        var scrollTop = $(window).scrollTop();
        if (home) {
          if (scrollTop >= 90) {
            $("#header_wrap").css("background-color", "#FFF");
            if ($("#logo").hasClass("white")) {
              $("#logo").removeClass("white");
            }
            $(
              "#topmenu a, #topmenu li.active a, #topmenu li.active a:hover, #topmenu span, #topmenu li.active span, #topmenu li.active span:hover"
            ).css("color", "#504f51");
            $("#topmenu li ul").css("background-color", "#FFF");
            if (!$("#topmenu a").hasClass("border-grey")) {
              $("#topmenu a").addClass("border-grey");
            }
            if (!$("ul.contact-social li a").hasClass("color-blue")) {
              $("ul.contact-social li a").addClass("color-blue");
            }

            if (!$(".btn.btn-cotacao").hasClass("border-blue")) {
              $(".btn.btn-cotacao").addClass("border-blue");
              $(".btn.btn-cotacao").addClass("color-blue");
            }
          } else {
            $("#header_wrap").css("background-color", "transparent");
            if (!$("#logo").hasClass("white")) {
              $("#logo").addClass("white");
            }
            $(
              "#topmenu a, #topmenu li.active a, #topmenu li.active a:hover, #topmenu span, #topmenu li.active span, #topmenu li.active span:hover"
            ).css("color", "#FFF");
            $("#topmenu li ul").css("background-color", "rgba(0,0,0,0.5)");
            if ($("#topmenu a").hasClass("border-grey")) {
              $("#topmenu a").removeClass("border-grey");
            }
            if ($("ul.contact-social li a").hasClass("color-blue")) {
              $("ul.contact-social li a").removeClass("color-blue");
            }

            if ($(".btn.btn-cotacao").hasClass("border-blue")) {
              $(".btn.btn-cotacao").removeClass("border-blue");
              $(".btn.btn-cotacao").removeClass("color-blue");
            }
          }
        } else {
          $("#header_wrap").next().css("margin-top", $("#header").height());
        }
      });

      $('.menuresp').css('max-height', $(window).height()-$("#header").height());

    })
    .trigger("resize");

  $(".menuresp").hide();

  $("#gotomenu").click(function () {
    $(".menuresp").css("visibility", "visible");
    $(".menuresp").slideToggle();
  });
});
