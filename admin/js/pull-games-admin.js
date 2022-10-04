jQuery(function( $ ) {
	'use strict';

	$('#experience_filter').selectize({
		placeholder: "Select an experience",
		plugins: ['remove_button'],
		valueField: 'id',
		labelField: 'title',
		searchField: ['title'],
		closeAfterSelect: true,
		load: function(input, callback) {
			if (!input.length) return callback();
			$.ajax({
				url: pullgames.ajaxurl,
				method: 'GET',
				data: {
					action: "get_experience_posts",
					query : input
				},
				error: function() {
					callback();
				},
				dataType: "json",
				success: function (result) {
					if(result.success){
						if (result.success.length > 10) {
							callback(result.success.slice(0, 10));
						}else{
							callback(result.success);
						}
					}
				}
			});
		}
	});

	$('#universe_ids').selectize({
		delimiter: ',',
		persist: false,
		create: function(input) {
			return {
				value: input,
				text: input
			}
		}
	});

	rowsSelection();
	function rowsSelection(){
		$("#select-all-games").on("change", function(){
			if($(this).is(":checked")){
				$("#resultset tbody").find(".select-game").each(function(){
					$(this).prop("checked", true);
				})
			}else{
				$("#resultset tbody").find(".select-game").each(function(){
					$(this).prop("checked", false);
				})
			}
		});
	}


	function pgProcessing(){
		$("#pullgames").find("button").each(function(){
			$(this).prop("disabled", true);
			$(".pg_loader").removeClass("dnone");
		})
	}
	function pgProcessOut(){
		$("#pullgames").find("button").each(function(){
			$(this).prop("disabled", false);
			$(".pg_loader").addClass("dnone");
		})
	}

	function getTableRows(){
		if($("#resultset tbody").find(".nores").length === 0){
			return $("#resultset tbody").children("tr").length;
		}else{
			return 0;
		}
	}

	function importTableRows(data, is_start_filter = false) { 
		if($("#resultset tbody").find(".nores").length > 0 || is_start_filter){
			$("#resultset tbody").html("")
		}

		if(data.length > 0){
			data.forEach(row => {
				$("#resultset tbody").find("tr[data-id='"+row.id+"']").remove();

				let htmlRow = `<tr data-id="${row.id}">
					<td><input type="checkbox" class="select-game" value="${row.id}"></td>
					<td>${row.name}</td>
					<td>${row.id}</td>
					<td>${row.creator}</td>
				</tr>`;

				$("#resultset tbody").append(htmlRow);
			});
		}

		$(".rowsCounts").text(getTableRows()); //Rows counter
	}

	$(document).on("click", ".search_universeids", function(){
		let ids = $("#universe_ids").val();
		$.ajax({
			type: "get",
			url: pullgames.ajaxurl,
			data: {
				action: "get_search_results",
				ids: ids,
				filter: 'id'
			},
			dataType: "json",
			beforeSend: pgProcessing(),
			success: function (response) {
				$("#resultset tbody").html("");
				$("#loadMoreGames").prop("disabled", true);
				$("#loadMoreGames").addClass("dnone");
				pgProcessOut();
				importTableRows(response.success);
			}
		});
	});

	var pages = 0;

	function getKeywordResult(keyword, maxRows, paginate, is_start_filter = false){
		$.ajax({
			type: "get",
			url: pullgames.ajaxurl,
			data: {
				action: "get_search_results",
				keyword: keyword,
				maxrows: maxRows,
				page: ((is_start_filter)?0: pages),
				filter: 'keyword'
			},
			dataType: "json",
			beforeSend: pgProcessing(),
			success: function (response) {
				pgProcessOut();
				if(response.success){
					importTableRows(response.success, is_start_filter);
					pages+=40;
					if(paginate){
						$("#loadMoreGames").removeAttr("disabled");
						$("#loadMoreGames").removeClass("dnone");
					}else{
						$("#loadMoreGames").prop("disabled", true);
						$("#loadMoreGames").addClass("dnone");
					}
				}
				if(response.norecord){
					$("#loadMoreGames").addClass("dnone");
				}
			}
		});
	}

	function getRecommendationsResult(experiences){
		$.ajax({
			type: "get",
			url: pullgames.ajaxurl,
			data: {
				action: "get_recommendations_results",
				experiences: experiences,
			},
			dataType: "json",
			beforeSend: pgProcessing(),
			success: function (response) {
				pgProcessOut();
				if(response.success){
					importTableRows(response.success, true);
					$("#loadMoreRecommendations").prop("disabled", true);
					$("#loadMoreRecommendations").addClass("dnone");
				}
				if(response.norecord){
					$("#loadMoreRecommendations").addClass("dnone");
				}
			}
		});
	}

	$(document).on("click", ".search_keyword", function(){
		let maxRows = 40;
		pages = 0;
		let keyword = $("#keyword_filter").val();

		let paginate = false;
		if($("#paginate_search").is(":checked")){
			paginate = true;
		}else{
			paginate = false;
		}

		getKeywordResult(keyword, maxRows, paginate, true);
	});

	$(document).on("click", ".search_recommendations", function(){
		let selectedExperiences = $("#experience_filter").val();
		getRecommendationsResult(selectedExperiences);
	});

	$("#loadMoreGames").on("click", function(){
		let maxRows = 40;
		let keyword = $("#keyword_filter").val();
		let paginate = false;
		if($("#paginate_search").is(":checked")){
			paginate = true;
		}else{
			paginate = false;
		}

		getKeywordResult(keyword, maxRows, paginate);
	});

	// Importing the games to posts
	$(document).on("click", "#import_games", function(){
		let selectedIds = [];
		$(".select-game:checked").each(function(){
			selectedIds.push($(this).val());
		});
		
		if(selectedIds.length > 0){
			$.ajax({
				type: "post",
				url: pullgames.ajaxurl,
				data: {
					action: "pullgames_imports",
					gamesIds: selectedIds
				},
				dataType: "json",
				beforeSend: pgProcessing(),
				success: function (response) {
					pgProcessOut();
					alert("Selected games are imported.")
				}
			});
		}
		
	});

});
