(function(a){a(document).ready(function(e){var b=e("#widgets-right"),d=b.find(".kcw-control-block, .kcwe"),c=b.find("h5");e(".hasdep",b).kcFormDep();e(".widgets-sortables",b).ajaxSuccess(function(){e(".hasdep",this).kcFormDep()});c.live("click",function(){e(this).next(".kcw-control-block").slideToggle("slow")});e(".kcw-control-block .del").live("click",function(j){j.preventDefault();var h=e(this),g=h.parent(),i=g.parent(),f=g.next(".row");g.slideUp(function(){if(!g.siblings(".row").length){g.find('input[type="text"]').val("");g.find('input[type="checkbox"]').prop("checked",false);g.find(".hasdep").trigger("change")}else{g.remove();if(f.length){i.kcReorder(h.attr("rel"),true)}}})});e(".kcw-control-block .add").live("click",function(h){h.preventDefault();var g=e(this),f=g.parent().prev(".row");if(f.is(":hidden")){f.slideDown()}else{$nu=f.clone(true).hide();f.after($nu);$nu.slideDown().kcReorder(g.attr("rel"),false)}})})})(jQuery);