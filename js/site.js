function getUsers(value, user){
	$.post("include/handlers/ajax_friend_search.php", {query:value, userLoggedIn:user}, function(data){
		$('.results').html(data);
	});
}

$(document).ready(function(){

	$('#search_text_input').on('focus', function(){
		$('.popover').hide();
	});

	$('#submit_profile_post').on('click', function(){
		$.ajax({
			url:'include/handlers/ajax_submit_post.php',
			type:'POST',
			data:$('form.profile_post').serialize(),
			success:function(response){
				$('#post_form').modal('hide');
				location.reload();
			},

			error:function(){
				alert('Failure');
			}

		})
	});

	$('.toggle_logreg').on('click', function(){
		$(this).parent().slideUp();
		$(this).parent().siblings('form').slideDown();
	});

	//// POPOVERS
	$("[data-toggle=popover]").popover({
	    html : true,
	    content: function() {
	        var content = $(this).attr("data-popover-content");
	        return $(content).children(".popover-body").html();
	    },
	    title: function() {
	        var title = $(this).attr("data-popover-content");
	        return $(title).children(".popover-heading").html();
	    }
	});

	$('html').on('click touchend', function (e) {
	    if ($(e.target).data('toggle') !== 'popover' && $(e.target).parents('.popover.in').length === 0) { 
	        $('[data-toggle="popover"]').popover('hide');
	    }
	});

	$('html').on('click touchend', function (e) {
	    if ($(this).attr('id') !== 'search_results') { 
	        $('#search_results').hide();
	        $('#search_text_input').val('');
	    }
	});
	
	$('body').popover({
	    selector: '[rel=popover]',
	    trigger: "click"
	}).on("show.bs.popover", function(e){
	    $("[rel=popover]").not(e.target).popover("destroy");
	    $(".popover").remove();                    
	});

	/// FIX: prevent having to double click to reopen after close
	$('body').on('hidden.bs.popover', function (e) {
	    $(e.target).data("bs.popover").inState.click = false;
	});

	//// AUTOFOCUS INPUT AFTER REOPENING SEARCH POPOVER
	$('[data-toggle="popover"]').on('shown.bs.popover', function () {
	    $('.popover').find("#searchPopoverInput").focus().select();
	});

	//// HIDE POPOVERS IF WINDOW IS RESIZED
	$(window).on('resize', function(){
	    $('[data-toggle="popover"]').popover('hide');
	});

});

function getLiveSearchUsers(value, user){

	var search_results = document.getElementById("search_results");
	search_results.style.display = "block"; 

	$.post("include/handlers/ajax_search.php", {query:value,userLoggedIn:user}, function(data){
		if($(".search_results_footer_empty")[0]){
			$(".search_results_footer_empty").toggleClass('search_results_footer');
			$(".search_results_footer_empty").toggleClass('search_results_footer_empty');
		}
		
		$('.search_results').html(data);
		$(".search_results_footer").html("<a href='search.php?q="+value+"'>See All Results</a>");

		if(data == ""){
			$(".search_results_footer").html('');
			$(".search_results_footer").toggleClass('search_results_footer_empty');
			$(".search_results_footer").toggleClass('search_results_footer');
		}
	});
}

$(document).ready(function(){
	  $('#search').on("click",(function(e){
	  $(".form-group").addClass("sb-search-open");
    	e.stopPropagation()
 	}));
  	
  	$(document).on("click", function(e) {
		if ($(e.target).is("#search") === false && $(".form-control").val().length == 0) {
    		$(".form-group").removeClass("sb-search-open");
    	}
	});

    $(".form-control-submit").click(function(e){
      $(".form-control").each(function(){
        if($(".form-control").val().length == 0){
          e.preventDefault();
          $(this).css('border', '2px solid red');
        }
    })
  })
})
