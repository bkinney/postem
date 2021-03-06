$(document).ready(function () {
	var tables = $('table.stickyHeader');
	tables.each(function(i){
		var table = tables[i];
		var tableClone = $(table).clone(true).empty().removeClass('stickyHeader');
		var theadClone = $(table).find('thead').clone(true);
		var stickyHeader =  $('<div></div>').addClass('stickyHeader hide').attr('aria-hidden', 'true');
		stickyHeader.append(tableClone).find('table').append(theadClone);
		$(table).after(stickyHeader);
		
		var tableHeight = $(table).height();
		var tableWidth = $(table).width() + Number($(table).css('padding-left').replace(/px/ig,"")) + Number($(table).css('padding-right').replace(/px/ig,"")) + Number($(table).css('border-left-width').replace(/px/ig,"")) + Number($(table).css('border-right-width').replace(/px/ig,""));
		
		var headerCells = $(table).find('thead th');
		var headerCellHeight = $(headerCells[0]).height();
		
		var no_fixed_support = false;
		if (stickyHeader.css('position') == "absolute") {
			no_fixed_support = true;
		}
		
		var stickyHeaderCells = stickyHeader.find('th');
		stickyHeader.css('width', tableWidth);
		var cellWidths = [];
		for (var i = 0, l = headerCells.length; i < l; i++) {
			cellWidths[i] = $(headerCells[i]).width();
		}
		for (var i = 0, l = headerCells.length; i < l; i++) {
			$(stickyHeaderCells[i]).css('width', cellWidths[i]);
		}
		
		var cutoffTop = $(table).offset().top;
		var cutoffBottom = tableHeight + cutoffTop - headerCellHeight;
		
		$(window).scroll(function() { 
		if($('table.stickyHeader:visible').length==0)return;
		var hPosition = $(window).scrollLeft();
		stickyHeader.css("left",(hPosition*-1 + 8)+"px");
		//var firstcol = $("table.stickyHeader tbody tr th");
		//firstcol.css("position","absolute");
		//firstcol.css("left","8px");
		var currentPosition = $(window).scrollTop();
			if (currentPosition > cutoffTop && currentPosition < cutoffBottom) {
				stickyHeader.removeClass('hide');
				if (no_fixed_support) {
					stickyHeader.css('top', currentPosition + 'px');
				}
			}
			else {
				stickyHeader.addClass('hide');
			}
		});
	});
});
