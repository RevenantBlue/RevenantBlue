var numOfRoots = 0
  , numOfChildren = [];

$(document).ready(function() {
	
	var editor 
	  , postId
	  , moveTopicId
	  , order
	  , sort
	  , waypointOptions
	  , permalinkWaypointOptions
	  , prevPost
	  , currentPost
	  , nextPost
	  , lastDir
	  , cpanelTabs
	  , uploader
	  , autoloadTopicsInProgress = false
	  , autoloadPostsInProgress
	  , lastScrollTop = 0
	
	// CKEditor the forum-editor
	if($("#forum-editor").length !== 0) {
		$("#forum-editor").ckeditor({
			contentsCss: HTTP_SERVER + "site/view/css/forum-cke.css",
			height: 192,
			forcePasteAsPlainText: true,
			disableNativeSpellChecker: false,
			removePlugins: 'contextmenu,liststyle,tabletools',
			allowedContent: {
				'b i ul ol strong em u s sub sup': true,
				'h1 h2 h3 p blockquote li a object div': {
					styles: 'text-align'
				},
				p: {
					attributes: 'class',
					classes: 'quote-citation'
				},
				a: { 
					attributes: 'href,target,class',
					classes:    'ui-icon,ui-icon-arrowreturnthick-1-w'
				},
				img: {
					attributes: 'src,alt',
					styles: 'width,height',
					classes: 'left,right'
				},
				iframe: {
					attributes: 'id,width,height,src,allowfullscreen,frameborder,title,name,tabindex,type,bgcolor,wmode,flashvars'
				}
			}
		});
	}
	
	// Define the CKEditor var and set the height of the message-editor
	if($("#forum-editor").length !== 0) {
		var editor = CKEDITOR.instances['forum-editor']
		  , dragStartOutside = true;
		editor.on("instanceReady", function(ev) {
			// Remove the resize handles, counter intuitive but ckeditor won't let me change the config dynamically for just one editor.
			$(".cke_resizer").remove();
			
			ev.editor.document.on('dragstart', function(ev) {
			   dragStartOutside = false;
			});
			ev.editor.document.on('drop', function(ev) {
			   if(dragStartOutside) {
				  ev.data.preventDefault(true);
			   }
			   dragStartOutside = true;
			});
			
			//console.log( editor.filter.allowedContent );
		});
	}
	
	// Build the forum if forumList has been set
	if(typeof(forumList) !== "undefined") {
		buildForum(forumList, false, function() {
			$("#forum").show();
		});
	}
	
	// Build subforums if subForumList is set
	if(typeof(subForumList) !== 'undefined') {
		buildForum(subForumList, false, function() {
			if($("#forum").length !== 0) {
				$("#forum").show();
			}
		});
	}
	
	// Set the tabs for the main forum display
	if($("#forum-tabs").length !== 0) {
		$("#forum-tabs").tabs({
			activate : function(e, ui) {                   
				if(ui.newPanel.prop('id') !== 'std-forum') {
					window.location.hash = ui.newPanel.prop('id');
				} else {
					window.location.hash = '';
				}
			}
		});
		
	}
	
	// If post id has been set set focus on element.
	if(typeof(url) !== 'undefined' && typeof(url.segment(5) !== 'undefined') && $('#forum-posts').length !== 0) {
		postId = url.segment('5');
		if($('#post-' + postId).length !== 0) {
			/*
			$('html, body').animate({
				scrollTop : $('#post-' + postId).offset().top - 200
			}, 1000);
			*/
			window.setTimeout(function() {
				$(window).scrollTop($('#post-' + postId).position().top);
			}, 150);
		}
	}
	
	// Global mouseup clicks
	$(document).mouseup(function(e) {
		var modWrap = $('.mod-opts-wrap');
		if(!modWrap.is(e.target) && modWrap.has(e.target).length === 0) {
			$('.mod-options').hide();
		}
	});
	
	// Click handlers
	$('#create-forum-topic').click(function(e) {
		e.preventDefault();
		$('#topic-panel').slideToggle();
	});
	
	$('#cancel-topic').click(function(e) {
		e.preventDefault();
		$('#topic-panel').slideToggle();
	});
	
	$('.create-forum-post').click(function(e) {
		e.preventDefault();
		$('#post-panel').slideToggle();
		editor.focus();
	});
	
	$('#cancel-post').click(function(e) {
		e.preventDefault();
		
		// If a post was being updated reset it.
		if($('#update-post').is(':visible')) {
			// Change the button from post to update
			$('#submit-post').show();
			$('#update-post').hide();
			// Reset the post id.
			$('#post-id').val('');
		}

		$('#post-panel').slideToggle();
	});
	
	// Favorite topic clicks
	$('#main-content').on('click', '.fav-stars', function(e) {
		var id = $(this).prop('id').replace('fav-', '')
		  , action;
		if($('#fav-' + id + ' > span').hasClass('favorited')) {
			action = 'remove-favorite';
		} else {
			action = 'favorite';
		}
		// Remove the no-hover class
		$('#fav-' + id + ' > .fav-star').removeClass('no-hover');
		// Update the favorite status.
		$.ajax({
			url: HTTP_SERVER + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'appRequest=' + encodeURIComponent(JSON.stringify({
				type      : 'forum',
				action    : action,
				topicId   : id,
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				if(response.error) {
					alert(response.error);
				} else {
					if(action === 'favorite') {
						//console.log('now favorited');
						$('#fav-' + id + ' > .fav-star').addClass('favorited no-hover');
					} else {
						//console.log('no unfavorited');
						$('#fav-' + id + ' > .fav-star').removeClass('favorited').addClass('no-hover');
					}
				}
			}
		});
	});
	
	// Class fix for mousing out on fav stars.
	$('.fav-stars').on('mouseout', function(e) {
		$('.fav-stars > .fav-star').removeClass('no-hover');
	});
	
	// Moderator options clicks 
	$('#forum-topics, .forum-topics').on('click', '.mod-opts-wrap', function(e) {
		e.preventDefault();
		var id = $(this).prop('id').replace('mod-opts-wrap-', '');
		//console.log(id);
		$('.mod-options').not($('#mod-options-' + id)).hide();
		if($('#mod-options-' + id).is(':visible')) {
			$('#mod-options-' + id).hide();
		} else {
			$('#mod-options-' + id).show();
		}
	});
	
	// Toggle moderation options when mousing in/out of topics table
	$('#forum-topics, .forum-topics').on('mouseover', '.topic-row' ,function(e) {
		//console.log('hovering');
		var topicId = $(this).prop('id').replace('topic-', '');
		$('#mod-opts-wrap-' + topicId).fadeIn('fast', function() {
			
		});
	});
	$('#forum-topics, .forum-topics').on('mouseleave', '.topic-row' ,function(e) {
		var topicId = $(this).prop('id').replace('topic-', '');
		if(!$('#mod-options-' + topicId).is(':visible')) {
			$('#mod-opts-wrap-' + topicId).fadeOut('fast', function() {
				
			});
		}
	});
	
	// Moderation: edit title clicks
	$('#forum-topics, .forum-topics').on('click', '.edit-topics', function(e) {
		var topicId = $(this).prop('id').replace('edit-topic-', '')
		  , currentTitle;
		  
		e.preventDefault();
		  
		currentTitle = $('#topic-title-' + topicId + ' > h4 > a').html();
		if($('#edit-topic-title-' + topicId).length === 0) {
			$('#topic-title-' + topicId + ' > h4').hide().before(
				'<div id="edit-title-wrap-' + topicId + '" class="edit-title-wrap">' +
					'<div class="inner">' +
						'<input type="text" id="edit-topic-title-' + topicId + '" class="edit-forum-title" value="' + $.trim(currentTitle) + '" />' +
						'<span id="update-topic-title-' + topicId + '" class="update-topic-title">Update</span>' +
					'</div>' +
				'</div>'
			);
		} else {
			$('#edit-title-wrap-' + topicId).remove();
			$('#topic-title-' + topicId + ' > h4').show();
			$('#topic-title-' + topicId + ' > h4 > a').html(response.title);
		}
	});
	
	// Moderation: update topic title
	$('#forum-topics, .forum-topics').on('click', '.update-topic-title', function(e) {
		var topicId = $(this).prop('id').replace('update-topic-title-', '')
		  , currentTitle;

		$.ajax({
			url: HTTP_SERVER + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'appRequest=' + encodeURIComponent(JSON.stringify({
				type      : 'forum',
				action    : 'update-topic-title',
				forumId   : RB.forum.forumId,
				topicId   : topicId,
				title     : $('#edit-topic-title-' + topicId).val(),
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				var response = JSON.parse(data);
				if(response.titleUpdated) {
					$('#edit-title-wrap-' + response.topicId).remove();
					$('#topic-title-' + response.topicId + ' > h4').show();
					$('#topic-title-' + response.topicId + ' > h4 > a').html(response.title);
				}
			}
		});
	});
	
	// Pin topics
	$('#forum-topics, .forum-topics').on('click', '.pin-topics', function(e) {
		var topicId = $(this).prop('id').replace('pin-topic-', '');
		e.preventDefault();
		
		$.ajax({
			url: HTTP_SERVER + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'appRequest=' + encodeURIComponent(JSON.stringify({
				type      : 'forum',
				action    : 'pin-topic',
				forumId   : RB.forum.forumId,
				topicId   : topicId,
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				var response = JSON.parse(data);
				$('#topic-tags-' + topicId).prepend(
					'<span id="pinned-' + response.topicId + '" class="forum-tags pinned">Pinned</span>'
				);
				// Remove the pin topic option from the moderation menu for this topic
				$('#pin-topic-' + topicId).remove();
				// Add the unpin topic to the moderation menu for this topic
				$('#edit-topic-' + topicId).after(
					'<li id="unpin-topic-' + response.topicId + '" class="unpin-topics">' +
						'<a href="#">' +
							'Unpin' +
						'</a>' +
						'<span class="sprites unpin"></span>' +
					'</li>'
				);
			}
		});
	});
	
	
	// Moderation: unpin topic clicks
	$('#forum-topics, .forum-topics').on('click', '.unpin-topics', function(e) {
		var topicId = $(this).prop('id').replace('unpin-topic-', '');
		e.preventDefault();
		
		$.ajax({
			url: HTTP_SERVER + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'appRequest=' + encodeURIComponent(JSON.stringify({
				type      : 'forum',
				action    : 'unpin-topic',
				forumId   : RB.forum.forumId,
				topicId   : topicId,
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				var response = JSON.parse(data);
				// Remove the pinned tag and remove the unpin topic option from the moderation menu for this topic
				$('#pinned-' + topicId + ', #unpin-topic-' + topicId).remove();
				// Add the unpin topic to the moderation menu for this topic
				$('#edit-topic-' + topicId).after(
					'<li id="pin-topic-' + response.topicId + '" class="pin-topics">' +
						'<a href="#">' +
							'Pin' +
						'</a>' +
						'<span class="sprites pin"></span>' +
					'</li>'
				);
			}
		});
	});
	
	// Moderation: lock topic clicks
	$('#forum-topics, .forum-topics').on('click', '.lock-topics', function(e) {
		var topicId = $(this).prop('id').replace('lock-topic-', '');
		
		e.preventDefault();
		
		$.ajax({
			url: HTTP_SERVER + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'appRequest=' + encodeURIComponent(JSON.stringify({
				type      : 'forum',
				action    : 'lock-topic',
				forumId   : RB.forum.forumId,
				topicId   : topicId,
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				var response = JSON.parse(data);
				if($('#topic-lock-' + topicId).length === 0) {
					$('#fav-' + topicId).append(
						'<span id="topic-lock-' + topicId + '" class="sprites locked"></span>'
					);
					// Remove the lock topic option from the moderator options
					$('#lock-topic-' + topicId).hide();
					// Add the unlock topic option to the moderator options.
					$('#unlock-topic-' + topicId).show();
				}
			}
		});
	});
	
	// Moderation: unlock topic clicks
	$('#forum-topics, .forum-topics').on('click', '.unlock-topics', function(e) {
		var topicId = $(this).prop('id').replace('unlock-topic-', '');
		
		e.preventDefault();
		
		$.ajax({
			url: HTTP_SERVER + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'appRequest=' + encodeURIComponent(JSON.stringify({
				type      : 'forum',
				action    : 'unlock-topic',
				forumId   : RB.forum.forumId,
				topicId   : topicId,
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				var response = JSON.parse(data);
				// Remove the lock icon from the topic.
				$('#topic-lock-' + topicId).remove();
				// Add the lock option to the moderator options.
				$('#unlock-topic-' + topicId).hide();
				$('#lock-topic-' + topicId).show();
			}
		});
	});
	
	// Moderation: move topic clicks
	$('#forum-topics, .forum-topics').on('click', '.move-topics', function(e) {
		moveTopicId = $(this).prop('id').replace('move-topic-', '');
		
		e.preventDefault();
		
		$('#move-topic-manager').dialog('option', 'title', 'Moving Topic: ' + $('#topic-title-' + moveTopicId + ' > h4 > a').text());
		$('#move-topic-manager').dialog('open');
		
	});
	
	// Move topic dialog
	$('#move-topic-manager').dialog({
		width       : 600,
		dialogClass : 'move-topic-mgr-dialog',
		modal       : true,
		autoOpen    : false,
		open        : function() {
			
		},
		close       : function() {
			moveTopicId = null;
		},
		buttons : [
			{
				'text'  : 'Move Topic',
				'id'    : 'move-topic',
				'class' : 'rb-btn blue-btn',
				'click' : function() {
					$.ajax({
						url: HTTP_SERVER + 'forums',
						type: 'POST',
						datatype: 'json',
						data: 'appRequest=' + encodeURIComponent(JSON.stringify({
							type      : 'forum',
							action    : 'move-topic',
							forumId   : RB.forum.forumId,
							topicId   : moveTopicId,
							moveTo    : $('#move-to-forum').val(),
							csrfToken : $('#csrf-token').val()
						})),
						success: function(data, textStatus, jqXHR) {
							var response = JSON.parse(data);
							if(response.topicMoved) {
								$('#topic-' + response.topicId).fadeOut('slow', function() {
									$('#topic-' + response.topicId).remove();
								});
								
								$('#move-topic-manager').dialog('close');
							}
						}
					});
				}
			},
			{
				'text'  : 'Close',
				'class' : 'rb-btn light-gray-btn',
				'click' : function() {
					$(this).dialog('close');
				}
			}
		]
	});
	
	// Moderation: delete topic clicks
	$('#forum-topics, .forum-topics').on('click', '.delete-topics', function(e) {
		var topicId = $(this).prop('id').replace('delete-topic-', '')
		  , confirmDelete;
		
		e.preventDefault();
		
		confirmDelete = confirm('Are you sure you want to delete this topic and all of its replies?');
		
		if(confirmDelete) {
			$.ajax({
				url: HTTP_SERVER + 'forums',
				type: 'POST',
				datatype: 'json',
				data: 'appRequest=' + encodeURIComponent(JSON.stringify({
					type      : 'forum',
					action    : 'delete-topic',
					forumId   : RB.forum.forumId,
					topicId   : topicId,
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					var response = JSON.parse(data);
					if(response.topicDeleted) {
						$('#topic-' + response.topicId).fadeOut('slow', function() {
							$('#topic-' + response.topicId).remove();
						});
					}
				}
			});
		}
	});
	
	// Submit new topic clicks
	$('#submit-topic').click(function(e) {
		e.preventDefault();
		
		var title = $('#poll-title').val(), choices = [];
		
		$('#main-form-action').val('post-topic');
		
		// Check if a poll has been created, if not then go ahead and submit the topic.
		if(!title) {
			$('#main-form').submit();
			return;
		}
		
		$('.poll-choice').each(function(e) {
			choices.push($(this).val());
		});
		
		$.ajax({
			url: HTTP_SERVER + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'appRequest=' + encodeURIComponent(JSON.stringify({
				type      : 'forum',
				action    : 'insert-poll',
				title     : title,
				choices   : choices,
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				var response = JSON.parse(data);
				//console.log(response);
				$('#poll-id').val(response.pollId);
			}
		}).done(function() {
			$('#main-form').submit();
		});
	});
	
	// Toggle preview-panel on the editor
	$('#toggle-preview').click(function(e) {
		if($('#editor-preview-wrap').is(':visible')) {
			$('#editor-preview-wrap').hide();
			$('#editor-wrap').css({
				width: '100%'
			});
			$('#toggle-preview').html('Show Preview');
		} else {
			$('#editor-preview-wrap').show();
			$('#editor-wrap').css({
				width: ''
			});
			$('#toggle-preview').html('Hide Preview');
		}
	});
	
	$("#topic-options-toggle").click(function(e) {
		//console.log('toggling');
		$("#topic-extras").toggle('slide', {
			direction : 'right'
		});
	});
	
	// Sync preview panel and editor
	if($("#forum-editor").length !== 0) {
		var editor = $("#forum-editor").ckeditor().editor;
		if (editor) {
			editor.on('change', function() {
				var editorContent = $("#forum-editor").val();
				$("#editor-preview").html(editorContent);
		  });
		}
	}
	
	// Resizable forum topic creator
	if($('#topic-panel').length !== 0) {
		$('#topic-panel').resizable({
			minHeight : 255,
			handles : 'n',
			start   : function(e, ui) {
				
			},
			resize  : function(e, ui) {
				
				$('#topic-panel').height(ui.size.height);
				$('#editor-preview').css('width', '');
				
				$('#topic-panel').css({
					'top'    : '',
					'bottom' : 0
				});
				
			},
			stop    : function(e, ui) {

			},
			alsoResize : '#cke_forum-editor > .cke_inner > .cke_contents, #editor-preview, #editors'
		});
	}

	// Resizable forum post creator
	if($('#post-panel').length !== 0) {
		$('#post-panel').resizable({
			minHeight : 255,
			handles :  'n',
			start   : function(e, ui) {

			},
			resize  : function(e, ui) {
				
				$('#post-panel').height(ui.size.height);
				$('#editor-preview').css('width', '');
				
				$('#post-panel').css({
					'top'    : '',
					'bottom' : 0
				});
				
			},
			stop    : function(e, ui) {

			},
			alsoResize : '#cke_forum-editor, .cke_inner > .cke_contents, #editor-preview, #editors'
		});
	}
	
	$('#open-poll-mgr').click(function(e) {
		e.preventDefault();
		//console.log('derp');
		$('#poll-manager').dialog('open');
	});
	
	// Topic poll modal
	$('#poll-manager').dialog({
		title       : 'Poll Manager',
		width       : 800,
		dialogClass : 'poll-mgr-dialog',
		modal       : true,
		autoOpen    : false,
		open        : function() {
			//console.log('opening');
		},
		buttons    : [
			{
				'text'  : 'Save Poll',
				'id'    : 'save-poll',
				'class' : 'rb-btn blue-btn',
				'click' : function() {
					$(this).dialog('close');
				}
			},
			{
				'text'  : 'Add Choice',
				'id'    : 'add-poll-choice',
				'class' : 'rb-btn light-gray-btn',
				'click' : function() {
					$('#poll-choices').append(
						'<div class="poll-choice-wrap clearfix">' +
							'<input type="text" class="poll-choice large-text fltlft">' +
							'<a class="poll-choice-del" href="#">Delete</a>' +
						'</div>'
					);
					$('.poll-choice-wrap:last-child > .poll-choice').focus();
				}
			},
			{
				'text'  : 'Close',
				'class' : 'rb-btn light-gray-btn',
				'click' : function() {
					$(this).dialog('close');
				}
			}
		]
	});
	
	$('.poll-choice-wrap').on('click', '.poll-choice-del', function(e) {
		e.preventDefault();
		$(this).parent().fadeOut('fast', function() {
			$(this).remove();
		});
	});
	
	$('#poll-vote').click(function(e) {
		e.preventDefault();
		
		var choiceId = $('.poll-choice:checked').val()
		  , pollId = $('#poll-id').val();
		
		//console.log(choiceId);
		
		$.ajax({
			url: HTTP_SERVER + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'appRequest=' + encodeURIComponent(JSON.stringify({
				type      : 'forum',
				action    : 'vote',
				pollId    : pollId,
				choiceId  : choiceId,
				topicId   : $('#topic-id').val(),
				forumId   : $('#forum-id').val(),
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				var response = JSON.parse(data);
				//console.log(response);
				$('#poll-id').val(response.pollId);
				
				if(typeof(response.voted) !== 'undefined') {
					$('#forum-poll-choices, #poll-vote, #poll-view-results').hide();
					$('#forum-poll-results').show();
					buildPoll(JSON.parse(response.poll));
				}
			}
		});
	});
	
	$('#poll-view-results').click(function(e) {
		e.preventDefault();
		if($('#forum-poll-choices').is(':visible')) {
			
			if($('#poll-vote').is(':visible')) {
				$('#poll-view-results').button('option', 'label', 'Back to voting');
			}
			
			$('#forum-poll-choices').hide();
			$('#forum-poll-results').show();
		} else {
			
			if($('#poll-vote').is(':visible')) {
				$('#poll-view-results').button('option', 'label', 'View Results');
			}
			
			$('#forum-poll-choices').show();
			$('#forum-poll-results').hide();
		}
	});
	
	// Dynamically load more topics by click.
	$('#load-more-topics').click(function(e) {
		e.preventDefault();
		
		// Load more topics clicks
		if(RB.forum.topicLimit + RB.forum.topicOffset < RB.forum.numOfTopics) {
			
			// Set the topic offset
			RB.forum.topicOffset += RB.forum.topicLimit;
			
			$.ajax({
				url: HTTP_SERVER + 'forums',
				type: 'POST',
				datatype: 'json',
				data: 'appRequest=' + encodeURIComponent(JSON.stringify({
					type      : 'forum',
					action    : 'load-more-topics',
					forumId   : RB.forum.forumId,
					limit     : RB.forum.topicLimit,
					offset    : RB.forum.topicOffset,
					order     : url.param('order'),
					sort      : url.param('sort'),
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					loadTopics(data, paginateTopics);
				}
			});
		}
	});
	
	if($('#forum-topics').length !== 0) {
		
		// Scroll to top on page load.
		// Timeouts are necessary for chrome and its stupid scroll position remembering argh!
		window.setTimeout(function() {
			$(document).scrollTop(0);
		}, 100);
		
		// Remember autoloader preference and set the global autoloadTopics var.
		if(typeof(localStorage) !== 'undefined') {
			if(typeof(localStorage.autoloadTopics) !== 'undefined') {
				if(localStorage.autoloadTopics == 'true') {
					RB.forum.autoloadTopics = true;
					$('#autoload-topics-toggle').removeClass('toggle-off').addClass('toggle-on').text('on');
				} else if(localStorage.autoloadTopics == 'false') {
					RB.forum.autoloadTopics = false;
					$('#autoload-topics-toggle').removeClass('toggle-on').addClass('toggle-off').text('off');
				}
			} else {
				localStorage.autoloadTopics = 'true';
				RB.forum.autloadTopics = true;
				$('#autoload-topics-toggle').removeClass('toggle-off').addClass('toggle-on').text('on');
			}
		} else {
			RB.forum.autoloadTopics = true;
			$('#autoload-topics-toggle').removeClass('toggle-off').addClass('toggle-on').text('on');
		}
		
		// Select the correct filter when topic loads
		$('#main-form').validate({
			rules: {
				'title' : {
					required  : true,
					minlength : 2
				}
			},
			messages : {
				'title' : {
					required  : 'Please provide a title for your topic',
					minlength : $.validator.format('Enter at least {0} characters')
				}
			}
		});
		
		order = $.url(window.location.href).param('order');
		
		switch(order) {
			case 'last_reply_date':
				$('#order-by-activity').addClass('selected');
				break;
			case 'date_posted':
				$('#order-by-date').addClass('selected');
				break;
			case 'num_of_posts':
				$('#order-by-replies').addClass('selected');
				break;
			case 'num_of_views':
				$('#order-by-views').addClass('selected');
				break;
			default:
				$('#topic-order-options li').removeClass('selected');
				$('#sort-by-activity').addClass('selected');
				$('#order-by-activity').addClass('selected');
				break;
		}
		
		// Load more topics when scrolling.
		waypointOptions = {
			offset : '80%'
		}
		
		$('#load-more-topics').waypoint(function(e, direction) {
			//console.log(RB.forum.autoloadTopics);
			//console.log('RB.forum.numOfTopics: ' + RB.forum.numOfTopics);
			//console.log('RB.forum.topicLimit: ' + RB.forum.topicLimit);
			//console.log('RB.forum.topicOffset: ' + RB.forum.topicOffset);
			//console.log('autoload: ' + RB.forum.autoloadTopics);
			//console.log('limit+offset: ' + (parseInt(RB.forum.topicLimit, 10) + parseInt(RB.forum.topicOffset, 10)));
			//console.log(direction);
			
			if(RB.forum.topicLimit <= RB.forum.numOfTopics && RB.forum.autoloadTopics && autoloadTopicsInProgress === false) {
				
				autoloadTopicsInProgress = true;
				
				$.ajax({
					url: HTTP_SERVER + 'forums',
					type: 'POST',
					datatype: 'json',
					data: 'appRequest=' + encodeURIComponent(JSON.stringify({
						type      : 'forum',
						action    : 'load-more-topics',
						forumId   : RB.forum.forumId,
						limit     : RB.forum.topicLimit,
						offset    : RB.forum.topicOffset,
						order     : url.param('order'),
						sort      : url.param('sort'),
						csrfToken : $('#csrf-token').val()
					})),
					success: function(data, textStatus, jqXHR) {
						var response = JSON.parse(data);
						
						if(RB.forum.topicLimit <= RB.forum.numOfTopics) {
							loadTopics(data, paginateTopics);
						} else {
							//console.log('disabling..');
							$('#load-more-topics').waypoint('disable');
							$('#load-more-topics').hide();
						}
						
						if(response.noMoreTopics) {
							//console.log('disabling..');
							$('#load-more-topics').waypoint('disable');
							$('#load-more-topics').hide();
						}
						
						// Refresh waypoints
						$.waypoints('refresh');
						
						// Update the limit and offset
						//console.log("b4topicOFfset: " + RB.forum.topicOffset);
						RB.forum.topicOffset += parseInt(RB.forum.topicLimit, 10);
						//console.log("a4topicOFfset: " + RB.forum.topicOffset);
						
						// Let the system know that autoloading has completed.
						autoloadTopicsInProgress = false;
					}
				});
			}
		}, waypointOptions);
		
		// Paginate the topics.
		paginateTopics();
	}
	
	// Topic autoloader click
	$('#autoload-topics-toggle').click(function(e) {
		var autoloadOn = $(this).hasClass('toggle-on');
		
		if(autoloadOn) {
			$(this).removeClass('toggle-on').addClass('toggle-off').text('off');
			RB.forum.autoloadTopics = false;
			// Update local storage
			if(typeof(localStorage) !== 'undefined') {
				localStorage.autoloadTopics = false;
			}
		} else {
			$(this).removeClass('toggle-off').addClass('toggle-on').text('on');
			RB.forum.autoloadTopics = true;
			// Update local storage
			if(typeof(localStorage) !== 'undefined') {
				localStorage.autoloadTopics = true;
			}
		}
		//console.log(localStorage.autoloadTopics);
	});
	
	// Topic order clicks
	$('#order-by-activity').click(function(e) {
		var topicUrl = buildTopicUrl('last_reply_date', 'desc');
		window.location.href = topicUrl;
		return false;
	});
	$('#order-by-date').click(function(e) {
		var topicUrl = buildTopicUrl('date_posted', 'desc');
		window.location.href = topicUrl;
		return false;
	});
	$('#order-by-replies').click(function(e) {
		var topicUrl = buildTopicUrl('num_of_posts', 'desc');
		window.location.href = topicUrl;
		return false;
	});
	$('#order-by-views').click(function(e) {
		var topicUrl = buildTopicUrl('num_of_views', 'desc');
		window.location.href = topicUrl;
		return false;
	});
	
	
	// Post toolbar menu clicks
	
	// Report post
	$('#forum-posts').on('click', '.post-menu-report', function(e) {
		var id = $(this).prop('id').replace('report-post-', '')
		  , reported = $(this).hasClass('reported');
		
		// Check to see if the post has been reported already
		if(reported) {
			$.ajax({
				url: HTTP_SERVER + 'forums',
				type: 'POST',
				datatype: 'json',
				data: 'appRequest=' + encodeURIComponent(JSON.stringify({
					type      : 'forum',
					action    : 'unreport-post',
					postId    : id,
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					var response = JSON.parse(data);
					if(typeof(response.error) !== 'undefined') {
						alert(response.error);
					} else {
						$('#report-post-' + id).text('Report').removeClass('reported');
					}
				}
			});
		} else {
			$.ajax({
				url: HTTP_SERVER + 'forums',
				type: 'POST',
				datatype: 'json',
				data: 'appRequest=' + encodeURIComponent(JSON.stringify({
					type      : 'forum',
					action    : 'report-post',
					postId    : id,
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					var response = JSON.parse(data);
					if(typeof(response.error) !== 'undefined') {
						alert(response.error);
					} else {
						$('#report-post-' + id).text('Reported').addClass('reported');
					}
				}
			});
		}

	});
	
	// Edit post
	$('#forum-posts').on('click', '.post-menu-edit', function(e) {
		
		var id = $(this).prop('id').replace('edit-post-', '')
		  , postContent;
		
		// Set the current post's content
		postContent = $('#post-content-' + id).html();
		
		// Change the button from post to update
		$('#submit-post').hide();
		$('#update-post').show();
		// Set the posts id that is being updated
		$('#post-id').val(id);
		// Show the panel and set the editor's content to that of the post being edited.
		$('#post-panel').slideDown();
		$('#forum-editor').val(postContent);
		$('#editor-preview').html(postContent);
	});
	
	$('#forum-posts').on('click', '.post-menu-delete', function() {
		var id = $(this).prop('id').replace('delete-post-', '')
		  , userId = $('#post-' + id + '-userid').val()
		$.ajax({
			url: HTTP_SERVER + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'appRequest=' + encodeURIComponent(JSON.stringify({
				type      : 'forum',
				action    : 'delete-post',
				forumId   : $('#forum-id').val(),
				topicId   : $('#topic-id').val(),
				postId    : id,
				userId    : userId,
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				var response = JSON.parse(data)
				  , userPostCount = parseInt($('.post-count-for-' + response.userId).html());
				$('#post-' + response.postId).fadeOut(1300, function() {
					var totalPosts, currentPost, deletedPostNum, postId;
					
					deletedPostNum = parseInt($('#post-permalink-' + response.postId).text().replace('#', ''));
					//console.log(deletedPostNum);
					
					$('#post-' + response.postId).remove();
					$('.post-count-for-' + response.userId).html(userPostCount - 1);
					// Reduce number of current posts.
					totalPosts = parseInt($('#total-posts').text());
					currentPost = parseInt($('#current-post').text());
					if(currentPost === totalPosts) {
						currentPost -= 1;
						$('#current-post').text(currentPost);
					}
					
					// Fix post ordering, will have to iterate through the affected posts.
					if(deletedPostNum !== totalPosts) {
						for(x = deletedPostNum; x <= totalPosts; x++) {
							//console.log(x);
							postId = $('#post-number-' + x).val();
							//console.log(postId);
							var newPostNum = x - 1;
							$('#post-permalink-' + postId).text('#' + newPostNum);
							$('#post-number-' + x).prop('id', 'post-number-' + newPostNum);
							
							deletedPostNum++;
						}
					}
					
					// Reduce total posts by one.
					totalPosts -= 1;
					$('#total-posts').text(totalPosts);
					
					// Rebind waypoints
					bindPostWaypointPermalink();
					bindPostWaypointFooter();
					
				});
			}
		});
	});
	
	// Quote post
	$('#forum-posts').on('click', '.post-menu-quote', function() {
		var id = $(this).prop('id').replace('quote-post-', '')
		  , quotedUser
		  , quotedPost
		  , quoteURL
		  , currentEditorHTML;
		
		// Get the quote's author
		quotedUser = $('#username-for-post-' + id).html();
		
		// Get the quote URL
		quoteURL = $('#post-permalink-' + id).prop('href');
		// Create a blockquote of the selected reply to quote.
		quotedPost = 
			'<p class="quote-citation">' +
				'<a href="' + quoteURL + '" class="ui-icon ui-icon-arrowreturnthick-1-w">&nbsp;</a>' +
				quotedUser + ' said:' +
			'</p>' +
			'<blockquote>' +
				$('#post-content-' + id).html() +
			'</blockquote>' +
			'<p> </p>';
		
		quotedPost = quotedPost.replace('<p>&nbsp;</p>', '');
		quotedPost = quotedPost.replace('<p></p>', '');
		
		currentEditorHTML = $('#forum-editor').val();
		
		//console.log(quotedPost);
		
		// Show the panel and set the editor's content to that of the post being edited.
		$('#post-panel').slideDown();
		$('#forum-editor').val(currentEditorHTML + '\n' + quotedPost);
		$('#editor-preview').html(currentEditorHTML + '\n' + quotedPost);
	});
	
	// Best answer
	$('#forum-posts').on('click', '.post-menu-best-answer', function() {
		var id = $(this).prop("id").replace("best-answer-post-", "")
		  , prevId;
		
		if($(".best-answer").length !== 0) {
			prevId = $(".best-answer").prop("id").replace("best-answer-", "");
		}
		
		//console.log(id);
		//console.log(prevId);
		
		$.ajax({
			url: HTTP_SERVER + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'appRequest=' + encodeURIComponent(JSON.stringify({
				type      : 'forum',
				action    : 'best-answer-post',
				prevId    : prevId,
				id        : id,
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				// Remove the best answer on the previous post and allow it to be re selected by making a button
				$("#best-answer-" + prevId).remove();
				
				$("#quote-post-" + prevId).after(
					'<li id="best-answer-post-' + prevId + '" class="post-menu-best-answer">' +
						'<span class="ui-icon ui-icon-check"></span>' +
						'<span>Best Answer</span>' +
					'</li>'
				);
				
				// Add the new best post and remove the best answer button for it.
				$("#posted-info-" + id).after(
					'<p id="best-answer-' + id + '" class="best-answer">' + 
						'<span class="ui-icon ui-icon-check"></span>' +
						'<span>Best Answer</span>' +
					'</p>'
				);
				
				$("#best-answer-post-" + id).remove();
			}
		});
	});
	
	// Forum posts specific requirements.
	if($('#forum-posts').length !== 0) {
		
		// Remember autoloader preference and set the global autoloadPosts var.
		if(typeof(localStorage) !== "undefined") {
			if(typeof(localStorage.autoloadPosts) !== "undefined") {
				//console.log(localStorage.autoloadPosts);
				if(localStorage.autoloadPosts == "true") {
					autoloadPosts = true;
					$("#autoload-posts-toggle").removeClass("toggle-off").addClass("toggle-on").text("on");
				} else if(localStorage.autoloadPosts == "false") {
					autoloadPosts = false;
					$("#autoload-posts-toggle").removeClass("toggle-on").addClass("toggle-off").text("off");
				}
			} else {
				localStorage.autoloadPosts = "true";
				autoloadPosts = true;
				$("#autoload-posts-toggle").removeClass("toggle-off").addClass("toggle-on").text("on");
			}
		} else {
			autloadPosts = true;
			$("#autoload-posts-toggle").removeClass("toggle-off").addClass("toggle-on").text("on");
		}
		
		if(!$("#current-post").text()) {
			$("#current-post").text(
				$("#forum-posts > .post-wrap:first > .inner > .post > .post-head > .post-permalink > a").text().replace("#", "")
			);
			
			setPostScrollerProgress($("#forum-posts > .post-wrap:first > .inner > .post > .post-head > .post-permalink > a").text().replace("#", ""));
		}
		
		// Topic autoloader click
		$("#autoload-posts-toggle").click(function(e) {
			var autoloadOn = $(this).hasClass("toggle-on");
			
			if(autoloadOn) {
				$(this).removeClass("toggle-on").addClass("toggle-off").text("off");
				autoloadPosts = false;
				// Update local storage
				if(typeof(localStorage) !== "undefined") {
					localStorage.autoloadPosts = false;
				}
			} else {
				$(this).removeClass("toggle-off").addClass("toggle-on").text("on");
				autoloadPosts = true;
				// Update local storage
				if(typeof(localStorage) !== "undefined") {
					localStorage.autoloadPosts = true;
				}
			}
			
			console.log(localStorage.autoloadPosts);
		});
		
		// Hide the scroller until we're ready to use it.
		$("#post-scroller").hide();
		
		// Timeouts are necessary for chrome and its stupid scroll position remembering argh!
		window.setTimeout(function() {
			$(document).scrollTop(0);
		}, 100);
		
		// Post scroller behavior and waypoints
		window.setTimeout(function() {
			bindPostWaypointPermalink();
			bindPostWaypointFooter();
			
			bindPostWaypointScrollUp();
			bindPostWaypointScrollDown();
			$("#post-scroller").show();
		}, 1000)
		
		// Set waypoint at top of the page to set the post number
		$('#main-inner').waypoint(function(direction) {
			if(direction === 'up') {
				$('#current-post').text(1);
			}
		});
		
		// Set the waypoint at the bottom of the forum that will lock the scroll from going past it.
		$('.end-of-forum').waypoint(function(direction) {
			if(direction === 'down') {
				// If the post number is equal to total posts stick the nav scroller
				$('#post-scroller').css({
					'position' : 'absolute'
				});
			} else if(direction === 'up') {
				$('#post-scroller').css({
					'position' : '',
					'bottom'   : ''
				});
			}
		}, {
			offset : '100%'
		});
	}
	
	// Forum profile specific requirements.
	if($('#forum-profile-menu').length !== 0) {
		// Tabs for forum user.
		$('#forum-profile-menu').tabs({
			activate : function(e, ui) {                   
				window.location.hash = ui.newPanel.prop('id');
			}
		}).addClass('ui-tabs-vertical ui-helper-clearfix');
		
		$('#forum-profile-menu li').removeClass('ui-corner-top').addClass('ui-corner-left');
		
		// Edit profile button click for the user view their own profile.
		$('#edit-my-profile').click(function(e) {
			e.preventDefault();
			window.location.href = HTTP_SERVER + 'cpanel';
		});
		
		// Send message button clicks in the forum user profile
		$("#open-messenger").click(function(e) {
			var sendToId;
			e.preventDefault();
			if($("#friend-id").length !== 0) {
				sendToId = $("#friend-id").val();
			}
			
			window.location.href = HTTP_SERVER + 'cpanel/messenger?sendTo=' + sendToId;
		});
		// Add friend button clicks in the forum user profile
		$('#add-friend').click(function(e) {
			e.preventDefault();
			var confirmRequest;

			$.ajax({
				url: HTTP_SERVER + 'forums',
				type: 'POST',
				datatype: 'json',
				data: 'appRequest=' + encodeURIComponent(JSON.stringify({
					type      : 'forum',
					action    : 'add-friend',
					friendId  : $('#friend-id').val(),
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					if(typeof(response.friendAdded) !== 'undefined') {
						$('#add-friend, #remove-friend').toggle();
						if($("#forum-profile-friends").length !== 0 && $("#user-recent-friends").length !== 0) {
							// Hide the no friends/recent friends elements.
							$("#no-friends, #no-recent-friends").hide();
							
							// Add recent friend which is the current user
							$("#user-recent-friends > .element-body").prepend(
								'<div id="recent-friend-' + response.userId + '" class="recent-friend">' +
									'<a href="' + HTTP_FORUM + 'user/' + response.userAlias + '/' + response.userId + '">' +
									
									'</a>' +
								'</div>'
							).hide().fadeIn(2000);
							
							// Add main friend entry
							$("#forum-profile-friends > .inner").prepend(
								'<div class="friend" id="friend-' + response.userId + '">' +
									'<a id="friend-avatar-' + response.userId + '" class="friend-avatar" href="' + HTTP_FORUM + 'user/' + response.userAlias + '/' + response.userId + '">' +
									
									'</a>' +
									'<a href="#">' +
										response.username +
									'</a>' +
								'</div>'
							).hide().fadeIn(2000);
							
							// Set avatars for the current user.
							if(typeof(response.avatar) !== "undefined" && typeof(response.avatarSmall !== "undefined")) {
								// Set the recent friend avatar.
								$("#recent-friend-" + response.userId + " > a").append(
									'<img title="' + response.username + '" alt="' + response.username + '" src="' + HTTP_AVATARS + response.avatarSmall + '" />'
								);
								// Set the main friend avatar
								$("#friend-avatar-" + response.userId).append(
									'<img title="' + response.username + '" alt="' + response.username + '" src="' + HTTP_AVATARS + response.avatar + '" />'
								);
							} else if(defaultAvatar && defaultAvatarSmall) {
								// Set the recent friend avatar.
								$("#recent-friend-" + response.userId + " > a").append(
									'<img title="' + response.username + '" alt="' + response.username + '" src="' + defaultAvatarSmall + '" />'
								);
								// Set the main friend avatar
								$("#friend-avatar-" + response.userId).append(
									'<img title="' + response.username + '" alt="' + response.username + '" src="' + defaultAvatar + '" />'
								);
							} else {
								// Set the no-avatar element for the recent friend/current user's avatar
								$("#recent-friend-" + response.userId + " > a").append(
									'<div class="no-avatar-small">' +
										'<div class="inner">' +
											'NA' +
										'</div>' +
									'</div>'
								);
								
								// Set the no-avatar element for the main friend/current uesr's avatar
								$("#friend-avatar-" + response.userId).append(
									'<div class="no-avatar">' +
										'<div class="inner">' +
											'<span>No Avatar</span>' +
										'</div>' +
									'</div>'
								);
							}
						}
					} else if(typeof(response.friendRequest) !== 'undefined') {
						alert('Friend request sent!');
					}
				}
			});
		});
		
		$('#remove-friend').click(function(e) {
			e.preventDefault();
			$.ajax({
				url: HTTP_SERVER + 'forums',
				type: 'POST',
				datatype: 'json',
				data: 'appRequest=' + encodeURIComponent(JSON.stringify({
					type      : 'forum',
					action    : 'remove-friend',
					friendId  : $('#friend-id').val(),
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					if(typeof(response.friendRemoved) !== 'undefined' && response.friendRemoved) {
						$('#add-friend, #remove-friend').toggle();
					}
					// Remove the user from the recent friends list if it exists.
					$("#recent-friend-" + response.userId).fadeOut('slow', function() {
						$(this).remove();
						//console.log($("#user-recent-friends > .element-body").children().length);
						// If the user has no more friends show the 'no more recent friends' message.
						if($("#user-recent-friends > .element-body").children().length === 1) {
							$("#no-friends, #no-recent-friends").show();
						}
					});
					$("#friend-" + response.userId).fadeOut('slow', function() {
						$(this).remove();
					});
				}
			});
		});
		
		$('#forum-profile-posts').on('click', '#load-more-user-posts', function(e) {
			$.ajax({
				url: HTTP_SERVER + 'forums',
				type: 'POST',
				datatype: 'json',
				data: 'appRequest=' + encodeURIComponent(JSON.stringify({
					type      : 'forum',
					action    : 'load-more-user-posts',
					offset    : postsOffset,
					userId    : profileUserId,
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					var res = JSON.parse(data), post, i;
					
					for(i = 0; i < res.userPosts.length; i += 1) {
						post = 
						'<div class="user-post">' +
							'<div class="user-post-topic">' +
								'<a href="' + HTTP_FORUM + 'topic/' + res.userPosts[i]['topic_alias'] + '/' + res.userPosts[i]['topic_id'] + '/' + res.userPosts[i]['id'] + '">' +
									'In Topic: ' + res.userPosts[i]['topic_title'] +
								'</a>' +
							'</div>' +
							'<div>' +
								'<div class="user-post-date">' +
									'Posted ' + res.userPosts[i]['date_posted'] +
								'</div>' +
								'<div class="post-content">' +
									res.userPosts[i]['post_content'] +
								'</div>' +
							'</div>' +
						'</div>';
						
						$('#forum-profile-posts > .inner > .user-post').last().after(post);
					}
					
					if(res.noMorePosts) {
						$('#load-more-user-posts').hide();
					}
					postsOffset += postsOffset;
				}
			});
		});
		
		$('#forum-profile-topics').on('click', '#load-more-user-topics', function(e) {
			$.ajax({
				url: HTTP_SERVER + 'forums',
				type: 'POST',
				datatype: 'json',
				data: 'appRequest=' + encodeURIComponent(JSON.stringify({
					type      : 'forum',
					action    : 'load-more-user-topics',
					offset    : topicsOffset,
					userId    : profileUserId,
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					var res = JSON.parse(data), topic, i;
					for(i = 0; i < res.userTopics.length; i += 1) {
						topic = 
						'<div class="user-topic">' +
							'<a href="' + HTTP_FORUM + 'topic/' + res.userTopics[i]['topic_alias'] + '/' + res.userTopics[i]['id'] + '">' +
								res.userTopics[i]['topic_title'] +
							'</a>' +
							'<div class="user-post-date">' +
								'Started ' + res.userTopics[i]['date_posted'] +
							'</div>' +
						'</div>'
					
						$('#forum-profile-topics > .inner > .user-topic').last().after(topic);
					}
					
					if(res.noMoreTopics) {
						$('#load-more-user-topics').hide();
					}
					
					topicsOffset += topicsOffset;
				}
			});
		});
	}
});

function buildTopicUrl(newOrder, newSort) {
	// Function build the url and set GET 'order' and 'sort' paramaeters for the server.
	var order
	  , sort
	  , currentUrl
	  , topicUrl;
	
	order = $.url(window.location.href).param('order');
	sort = $.url(window.location.href).param('sort');
	//console.log(newOrder);
	//console.log(newSort);
	//console.log(order);
	//console.log(sort);
	if(order && sort) {
		topicUrl = window.location.href;
		topicUrl = topicUrl.replace(new RegExp(order, 'g'), newOrder);
		topicUrl = topicUrl.replace(new RegExp(sort, 'g'), newSort );
		//console.log(topicUrl);
	} else {
		topicUrl = window.location.href + '?order=' + newOrder + '&sort=' + newSort;
	}
	
	return topicUrl;
}

function loadTopics(data, callback) {
	var response = JSON.parse(data)
	  , favClass;
	
	if(response.noMoreTopics) {
		$('#load-more-topics').fadeOut('slow');
	}
	// Iterate through the loaded topics.
	for(x = 0; x < response.topicList.length; x++) {
		// Check if the topic is favorited, if it is set the favClass.
		if($.inArray(response.topicList[x].id, RB.forum.favoriteTopics) !== -1) {
			favClass = 'favorited';
		} else {
			favClass = '';
		}
		$('#forum-topics > tbody:last').append(
			'<tr id="topic-' + response.topicList[x].id + '" class="topic-row" style="display: none;" data-num-of-posts="' + response.topicList[x].num_of_posts + '" data-alias="' + response.topicList[x].topic_alias + '" data-paginated="false">' +
				'<td id="fav-' + response.topicList[x].id + '" class="fav-stars" title="Add/Remove favorite topic">' +
					'<span class="fav-star' + ' ' + favClass + '">' +
						'☆' +
					'</span>' +
				'</td>' +
				'<td id="topic-title-' + response.topicList[x].id + '" class="topic-title">' +
					'<h4>' +
						'<a href="' + HTTP_FORUM + 'topic/' + response.topicList[x].topic_alias + '/' + response.topicList[x].id + '">' + 
							response.topicList[x].topic_title + 
						'</a>' +
					'</h4>' +
					'<p>' +
						'<span>Started by </span>' +
						'<a href="' + HTTP_SERVER + 'forums/user/' + response.topicList[x].last_reply_username_alias + '/' + response.topicList[x].last_reply_user_id + '">' + 
							response.topicList[x].username + 
						'</a>' +
						'<span>, ' + response.topicList[x].date_posted + '</span>' +
					'</p>' +
				'</td>' +
				'<td id="topic-tags-' + response.topicList[x].id + '" class="topic-tags">' +
				
				'</td>' +
				'<td class="topic-info">' +
					'<ul>' +
						'<li>' +
							'<span>' +
								response.topicList[x].num_of_posts + ' replies' +
							'</span>' +
						'</li>' +
						'<li>' +
							'<span>' +
								response.topicList[x].num_of_views + ' views</span>' +
							'</span>' +
						'</li>' +
					'</ul>' +
				'</td>' +
				'<td id="last-reply-column-' + response.topicList[x].id + '" class="topic-last-reply">' +
					'<div id="last-reply-avatar-' + response.topicList[x].id + '" class="last-reply-avatar fltlft">' +
						'<a href="' + HTTP_SERVER + 'forums/user/' + response.topicList[x].last_reply_username_alias + '/' + response.topicList[x].last_reply_user_id + '">' +
						
						'</a>' +
					'</div>' +
					'<ul id="last-reply-info-' + response.topicList[x].id + '" class="last-reply-info">' +

					'</ul>' +
				'</td>' +
			'</tr>'
		);
		
		if(response.topicList[x].last_reply_username) {
			
			// Add the last reply avatar
			if(response.topicList[x].avatar_small) {
				$("#last-reply-avatar-" + response.topicList[x].id + " > a").append(
					'<img src="' + HTTP_AVATARS + response.topicList[x].avatar_small + '" alt="' + response.topicList[x].last_reply_username + '" title="' + response.topicList[x].last_reply_username + '" />'
				);
			} else if(typeof(defaultAvatarSmall) !== "undefined") {
				$("#last-reply-avatar-" + response.topicList[x].id + " > a").append(
					'<img src="' + defaultAvatarSmall + '" alt="' + response.topicList[x].last_reply_username + '" title="' + response.topicList[x].last_reply_username + '" />'
				);
			} else {
				$("#last-reply-avatar-" + response.topicList[x].id + " > a").append(
					'<div class="no-avatar-small">' +
						'<div class="inner">' +
							'NA' +
						'</div>' +
					'</div>'
				);
			}
			
			// Append last reply username
			$('#last-reply-info-' + response.topicList[x].id).append(
				'<li>' +
					'<a href="' + HTTP_SERVER + 'forums/user/' + response.topicList[x].last_reply_username_alias + '/' + response.topicList[x].last_reply_user_id + '">' +
						response.topicList[x].last_reply_username +
					'</a>' +
				'</li>'
			);
		}
		$('#last-reply-info-' + response.topicList[x].id).append(
			'<li>' +
				'<span>' +
						response.topicList[x].last_reply_date +
				'</span>' +
			'</li>'
		);
		
		// If topic is pinned
		if(response.topicList[x].pinned != 0) {
			$('#topic-tags-' + response.topicList[x].id).prepend(
				'<span id="pinned-' + response.topicList[x].id + '" class="forum-tags pinned">Pinned</span>'
			);
		}

		// Set the moderator options for the topic.
		if(typeof(RB.forum.moderatorPerms) !== 'undefined' && RB.forum.moderatorPerms) {
			// If the user is a moderator add the mod-options
			$('#last-reply-column-' + response.topicList[x].id).after(
				'<td class="mod-column">' +
					'<div id="mod-opts-wrap-' + response.topicList[x].id + '" class="mod-opts-wrap" style="display: none;">' +
						'<div class="mod-option-icon sprites" title="Moderator Actions"></div>' +
						'<span class="arrow-down"></span>' +
						'<ul id="mod-options-' + response.topicList[x].id + '" style="display: none;" class="mod-options">' +
						
						'</ul>' +
					'</div>' +
				'</td>'
			);
			if(RB.forum.moderatorPerms[response.topicList[x].forum_id].edit_topics == 1) {
				$('#mod-options-' + response.topicList[x].id).append(
					'<li id="edit-topic-' + response.topicList[x].id + '" class="edit-topics">' +
						'<a href="#">' +
							'Edit title' +
						'</a>' +
						'<span class="ui-icon ui-icon-pencil"></span>' +
					'</li>'
				);
			}
			if(RB.forum.moderatorPerms[response.topicList[x].forum_id].pin_topics == 1) {
				if(response.topicList[x].pinned == 0) {
					$('#mod-options-' + response.topicList[x].id).append(
						'<li id="pin-topic-' + response.topicList[x].id + '" class="pin-topics">' +
							'<a href="#">' +
								'Pin' +
							'</a>' +
							'<span class="sprites pin"></span>' +
						'</li>'
					);
				} else {
					$('#mod-options-' + response.topicList[x].id).append(
						'<li id="unpin-topic-' + response.topicList[x].id + '" class="unpin-topics">' +
							'<a href="#">' +
								'Unpin' +
							'</a>' +
							'<span class="sprites unpin"></span>' +
						'</li>'
					);
				}
			}
			if(RB.forum.moderatorPerms[response.topicList[x].forum_id].close_topics == 1) {

				$('#mod-options-' + response.topicList[x].id).append(
					'<li id="lock-topic-' + response.topicList[x].id + '" class="lock-topics">' +
						'<a href="#">' +
							'Lock' +
						'</a>' +
						'<span class="ui-icon ui-icon-locked"></span>' +
					'</li>'
				);
				$('#mod-options-' + response.topicList[x].id).append(
					'<li id="unlock-topic-' + response.topicList[x].id + '" class="unlock-topics">' +
						'<a href="#">' +
							'Unlock' +
						'</a>' +
						'<span class="ui-icon ui-icon-unlocked"></span>' +
					'</li>'
				);
				if(response.topicList[x].locked == 0) {
					$('#unlock-topic-' + response.topicList[x].id).hide();
				} else {
					$('#lock-topic' + response.topicList[x].id).hide();
				}
			}
			if(RB.forum.moderatorPerms[response.topicList[x].forum_id].move_topics == 1) {
				$('#mod-options-' + response.topicList[x].id).append(
					'<li id="move-topic-' + response.topicList[x].id + '" class="move-topics">' +
						'<a href="#">' +
							'Move' +
						'</a>' +
						'<span class="ui-icon ui-icon-transferthick-e-w"></span>' +
					'</li>'
				);
			}
			if(RB.forum.moderatorPerms[response.topicList[x].forum_id].toggle_topic_visibility == 1) {
				$('#mod-options-' + response.topicList[x].id).append(
					'<li id="hide-topic-' + response.topicList[x].id + '" class="hide-topics">' +
						'<a href="#">' +
							'Hide' +
						'</a>' +
						'<span class="ui-icon ui-icon-radio-on"></span>' +
					'</li>'
				);
			}
			if(RB.forum.moderatorPerms[response.topicList[x].forum_id].delete_topics == 1) {
				$('#mod-options-' + response.topicList[x].id).append(
					'<li id="delete-topic-' + response.topicList[x].id + '" class="delete-topics"' +
						'<a href="#">' +
							'Delete' +
						'</a>' +
						'<span class="ui-icon ui-icon-trash"></span>' +
					'</li>'
				);
			}
		}
	}
	
	// Iterate through the topics and show them one at a time.
	for(x = 0; x < response.topicList.length; x++) {
		//console.log($("#post-" + postIds[x]).length);
		//console.log(postIds[x]);
		$("#topic-" + response.topicList[x].id).fadeIn(1000, function() {
			
		});
	}
	
	callback();
}

function buildForum(forumArr, isChild, callback) {
	//console.log(forumArr);
	for(forum in forumArr) {
		//console.log(forumArr[forum].id);
		//console.log(forumArr.id);
		//console.log(forumArr[forum]);
		if(!isChild && forumArr[forum].id && forumArr[forum].rootDistance == 0) {
			// Add a root node to the fourm table.
			$('#forum').append(
				'<li id="forum-' + forumArr[forum].id + '" class="root forum-node">' + 
					'<div class="root-node clearfix">' +
						'<div class="forum-desc">' +
							'<div class="forum-title">' + 
								'<h3>' + 
									'<a href="' + HTTP_SERVER + 'forums/' + forumArr[forum].alias + '/' + forumArr[forum].id + '">' + forumArr[forum].title + '</a>' + 
								'</h3>' +
							'</div>' +
						'</div>' +
					'</div>' +
				'</li>'
			);
			
			// Increment the number of roots
			numOfRoots += 1;
			
			// If this is the first root add a class, for styling purposes
			if(numOfRoots === 1) {
				$('#forum-' + forumArr[forum].id + ' > div.root-node').addClass('first-root');
			} else {
				$('#forum-' + forumArr[forum].id + ' > div.root-node').addClass('other-roots');
			}
			
			if(typeof(forumArr[forum].children) === 'object') {
				$('#forum-' + forumArr[forum].id).append(
					'<table id="forum-' + forumArr[forum].id + '-children" class="forum-children">' +
					'</table>'
				);
				for(child in forumArr[forum].children) {
					if(typeof(forumArr[forum]) !== 'undefined') {
						//console.log(forumArr[forum].children);
						buildForum(forumArr[forum].children, true);
					}
				}
			}
		} else if(isChild === true) {
			//console.log(forumArr);
			if(forumArr[forum].id && $('#forum-' + forumArr[forum].id).length === 0 && typeof(forumArr[forum]) !== 'undefined') {
				// Add a forum to the root's children table.
				if(forumArr[forum].rootDistance < 2) {
					if(!forumArr[forum].lastReplyDate) {
						forumArr[forum].lastReplyDate = 'No Topics Posted';
					}
					if(!forumArr[forum].lastReplyDate) {
						forumArr[forum].lastReplyDate = '';
					}
					$('#forum-' + forumArr[forum].ancestor + '-children').append(
						'<tr id="forum-' + forumArr[forum].id + '" class="forum-child forum-node forum-depth-' + forumArr[forum].rootDistance + '">' +
							'<td id="forum-attribs-' + forumArr[forum].id + '" class="forum-title">' + 
								'<h4>' + 
									'<a href="' + HTTP_SERVER + 'forums/' + forumArr[forum].alias + '/' + forumArr[forum].id + '">' + forumArr[forum].title + '</a>' + 
								'</h4>' +
							'</td>' +
							'<td></td>' +
							'<td class="forum-topic-reply-count">' +
								'<ul>' +
									'<li>' +
										forumArr[forum].numOfTopics + ' topics' +
									'</li>' +
									'<li>' +
										forumArr[forum].numOfPosts + ' replies' +
									'</li>' +
								'</ul>' +
							'</td>' +
							'<td class="forum-last-post">' +
								'<div id="last-reply-avatar-' + forumArr[forum].id + '" class="last-reply-avatar fltlft">' +
								
								'</div>' +
								'<ul id="last-reply-info-' + forumArr[forum].id + '" class="last-reply-info">' +

								'</ul>' +
							'</td>' +
						'</tr>'
					);
					// Add the forum description.
					$('#forum-attribs-' + forumArr[forum].id).append(
						'<div id="forum-description-' + forumArr[forum].id + '" class="forum-description">' +
							forumArr[forum].description +
						'</div>'
					);
					
					// Check if last reply info should be hidden, if not add the last reply info.
					if(!forumArr[forum].hideLastPostInfo || forumArr[forum].hideLastPostInfo == 0) {
						
						if(typeof(forumArr[forum].lastReplyUsername) !== "undefined" && forumArr[forum].lastReplyUsername) {
							//console.log(forumArr[forum].lastReplyUsername);
							$("#last-reply-info-" + forumArr[forum].id).append(
								'<li>' +
									'<a href="' + HTTP_FORUM + 'topic/' + forumArr[forum].lastReplyTopicAlias + '/' + forumArr[forum].lastReplyTopic + '">' +
										forumArr[forum].lastReplyTopicTitle +
									'</a>' +
								'</li>'
							);
							$("#last-reply-info-" + forumArr[forum].id).append(
								'<li>' +
									'<a href="' + HTTP_SERVER + 'forums/user/' + forumArr[forum].lastReplyAlias + '/' + forumArr[forum].lastReplyUserId + '">' +
										forumArr[forum].lastReplyUsername +
									'</a>' +
								'</li>'
							);
						} else {
							$("#last-reply-info-" + forumArr[forum].id).css({
								"margin-top" : "10px"
							});
						}
						
						$("#last-reply-info-" + forumArr[forum].id).append(
							'<li>' +
								forumArr[forum].lastReplyDate +
							'</li>'
						);
						
						// Add the last reply avatar
						if(forumArr[forum].avatarSmall && forumArr[forum].lastReplyUserId) {
							$("#last-reply-avatar-" + forumArr[forum].id).append(
								'<img src="' + HTTP_AVATARS + forumArr[forum].avatarSmall + '" alt="' + forumArr[forum].lastReplyUsername + '" title="' + forumArr[forum].lastReplyUsername + '" />'
							);
						} else if(forumArr[forum].lastReplyUserId && typeof(defaultAvatarSmall) !== "undefined") {
							$("#last-reply-avatar-" + forumArr[forum].id).append(
								'<img src="' + defaultAvatarSmall + '" alt="' + forumArr[forum].lastReplyUsername + '" title="' + forumArr[forum].lastReplyUsername + '" />'
							);
						} else {
							$("#last-reply-avatar-" + forumArr[forum].id).append(
								'<div class="no-avatar-small">' +
									'<div class="inner">' +
										'NA' +
									'</div>' +
								'</div>'
							);
						}
					}
					
					//console.log(forumArr[forum].children.length);
					numOfChildren.total = numOfChildren.currentTotal = forumArr[forum].children.length;
					// Add a subforum container if forum contains subforums and iterate through subforums
					if(forumArr[forum].children.length > 0) {
						$('#forum-description-' + forumArr[forum].id).before(
							'<table id="forum-' + forumArr[forum].id + '-children" class="sub-forums"></table>'
						);
						//console.log(forumArr[forum]);
						for(child in forumArr[forum].children) {
							//console.log(forumArr[forum]);
							//console.log(typeof(forumArr[forum].children));
							if(typeof(forumArr[forum]) !== 'undefined') {
								if(typeof(forumArr[forum].children) === 'object') {
									buildForum(forumArr[forum].children, true);
								}
							}
						}
					}
				} else {
					// Add the subforums for a forum.
					// Start with changes if this is the first subforum in a group of subforums.
					if(numOfChildren.total === numOfChildren.currentTotal) {
						$('#forum-' + forumArr[forum].ancestor + '-children').append(
							'<tr>' +
								'<td id="sub-forums-for-' + forumArr[forum].ancestor + '"></td>' +
							'</tr>'
						);
						$('#sub-forums-for-' + forumArr[forum].ancestor).append(
							'<span class="down-one-level"></span>'
						);
					}
					//console.log(forumArr[forum]);
					$('#sub-forums-for-' + forumArr[forum].ancestor).append(
						'<a id="subforum-title-' + forumArr[forum].id + '" href="' + HTTP_SERVER + 'forums/' + forumArr[forum].alias + '/' + forumArr[forum].id + '">' + forumArr[forum].title + '</a>'
					);
					//console.log(numOfChildren);
					numOfChildren.currentTotal -= 1;
					//console.log(numOfChildren);
					if(numOfChildren.currentTotal !== 0) {
						$('#sub-forums-for-' + forumArr[forum].ancestor).append(
							'<span>, </span>'
						);
					}
				}
			}
			if(typeof(forumArr[forum]) !== 'undefined') {
				if(typeof(forumArr[forum].children) === 'object' && forumArr[forum].rootDistance < 2) {
					$('#forum-' + forumArr[forum].id).after(
						'<tr>' +
							'<td>' +
								'<table id="forum-' + forumArr[forum].id + '-children">' +
								'</table>' +
							'</td>' +
						'</tr>'
					);
				}
			}
		}
	}
	if(typeof(callback) === 'function') {
		callback();
	}
}

function bindPostWaypointPermalink() {
	// When scrolling up.
	$('.post-permalink').waypoint(function(direction) {
		var id = $(this).children('a').prop('id').replace('post-permalink-', '')
		  , postNumber
		  , totalPosts;
		
		postNumber = parseInt($('#post-permalink-' + id).text().replace('#', '')); 
		totalPosts = parseInt($('#total-posts').text());
		
		if(direction === 'up') {
			
			//console.log(postNumber);
			
			if(!$('#current-post').hasClass('rebinding')) {
				$('#current-post').text(postNumber);
			}
			
			// Set the background color to show the progress.
			setPostScrollerProgress(postNumber);
			
			// If the post number is equal to total posts or autloading posts is disabled stick the nav scroller
			postNumberStr = new String(postNumber + 1);
		}
	}, {
		offset: '25%'
	});
}

function bindPostWaypointFooter() {
	$('.post-footer').each(function() {
		
		// When scrolling down
		$(this).waypoint(function(direction) {
			
			var id = $(this).prop('id').replace('post-footer-', '')
			  , postNumber
			  , totalPosts
			  , postNumberStr;
			  
			postNumber = parseInt($('#post-permalink-' + id).text().replace('#', ''), 10);
			totalPosts = parseInt($('#total-posts').text() , 10);
			
			//console.log('postNumber', postNumber);
			
			if(direction === 'down') {
				
				$('#current-post').text(postNumber);
				
				// Set the background color to show the progress.
				if(postNumber !== totalPosts && postNumber !== 1) {
					setPostScrollerProgress(postNumber);
				} else {
					setPostScrollerProgress(postNumber);
				}
			}
		}, {
			offset : '75%'
		});
	});
}

// Load more topics when reaching the bottom.
function bindPostWaypointScrollUp() {
	$('.load-more-posts-up').waypoint(function(direction) {
		//console.log('Loading more posts up');
		var firstPostId = $('#forum-posts > .post-wrap').first().prop('id').replace('post-', '')
		  , postNumber = parseInt($('#post-permalink-' + firstPostId).text().replace('#', ''), 10)
		  , totalPosts = parseInt($('#total-posts').text(), 10)
		  , topPostId = $('#forum-posts div:first-child').prop('id')
		  , x;
		
		if(direction === 'up' && postNumber !== 1 && noMorePostsUp.length === 0 && autoloadPosts
		&& (typeof(autoloadPostsInProgress) === "undefined" || !autoloadPostsInProgress)) {
			
			//console.log('numOfPosts: ' + numOfPosts);
			//console.log('limit: ' + upLimit);
			//console.log('offset: ' + upOffset);
			//console.log('offset-limit: ' + (parseInt(upOffset, 10) - parseInt(upLimit, 10)));
			//console.log(direction);
			//console.log('loading more posts up');
			
			// Show the loading indicator
			$('#posts-loading').Loadingdotdotdot({
				'speed': 400,
				'maxDots': 4,
				'word': 'Loading '
			}).show();
			
			// Update the offset
			upOffset -= upLimit;
			
			if(upOffset < 0) {
				upLimit = upLimit - Math.abs(upOffset);
				upOffset = 0;
			}
			
			// Destroy old waypoints before ajax call to avoid viewport change conflicts.
			$('.post-permalink').waypoint('destroy');
			$('.post-footer').waypoint('destroy');
			
			// If loading more posts scrolling up.
			$.ajax({
				url: HTTP_SERVER + 'forums',
				type: 'POST',
				datatype: 'json',
				data: 'appRequest=' + encodeURIComponent(JSON.stringify({
					type       : 'forum',
					action     : 'load-more-posts-up',
					fourmId    : $('#forum-id').val(),
					topicId    : $('#topic-id').val(),
					topicAlias : $('#topic-alias').val(),
					limit      : upLimit,
					offset     : upOffset,
					numOfPosts : numOfPosts,
					topPostId  : topPostId,
					csrfToken  : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					
					var response = JSON.parse(data);
					
					// Let the client know that an autoload is in progress (prevents triggering other autoloads due to page jumping in some browsers)
					autoloadPostsInProgress = true;
					
					// Temporarily
					//console.log(response.modPerms);
					addPosts(response.posts, response.topicAlias, response.modPerms, 'up', response, function(response) {
						
						var firstPostId;
						
						//console.log('response.topPostId', $('#' + response.topPostId));
						// Keep viewport
						//$('#forum-posts').scrollTop($('#' + response.topPostId).offset().top);
						
						// Revise the number of posts and current offset.
						numOfPosts = parseInt(numOfPosts) + parseInt(limit);
						
						// Update the first post.
						window.setTimeout(function() {
							firstPostId = $('#forum-posts div:first-child').prop('id').replace('post-', '');
							firstPost = parseInt($('#post-permalink-' + firstPostId).text().replace('#', ''));
							//console.log(firstPost);
							
							// Rebind waypoints
							bindPostWaypointPermalink();
							bindPostWaypointFooter();
							
						}, 10);
						
						
						if(typeof(response.noMorePostsUp) !== 'undefined') {
							noMorePostsUp = true;
						}
						
						
						window.setTimeout(function() {
							$('#posts-loading').fadeOut();
							// Let the client know that autoloading has completed.
							autoloadPostsInProgress = '';
						}, 2000);
						
						
					});
					
					return false;
				}
			});
		}
	});
}

function bindPostWaypointScrollDown() {
	$('#forum-posts').waypoint(function(direction) {
		
		var postNumber = parseInt($('#current-post').text(), 10)
		  , totalPosts = parseInt($('#total-posts').text(), 10)
		  , bottomPostId = $('#forum-posts div').prop('id');
		
		//console.log(noMorePostsDown);
		//console.log(bottomPostId);
		//console.log(direction);
		if(direction === 'down') {
			//console.log(postNumber);
		}
		
		if(direction === 'down' && postNumber !== totalPosts && noMorePostsDown.length === 0 && $("#post-number-" + totalPosts).length === 0 
		&& autoloadPosts && (typeof(autoloadPostsInProgress) === "undefined" || !autoloadPostsInProgress)) {
			//console.log(postNumber);
			//console.log(totalPosts);
			
			//console.log('numOfPosts: ' + numOfPosts);
			//console.log('totalPosts: ' + totalPosts);
			//console.log('limit: ' + downLimit);
			//console.log('offset: ' + downOffset);
			//console.log('offset+limit: ' + (parseInt(downOffset, 10) + parseInt(downLimit, 10)));
			//console.log(direction);
			//console.log('loading more posts down');
			
			// Show the loading indicator
			$('#posts-loading').Loadingdotdotdot({
				'speed': 400,
				'maxDots': 4,
				'word': 'Loading '
			}).show();
			
			// Update the offset
			downOffset += downLimit;
			
			if(downOffset > totalPosts) {
				downLimit = downOffset - totalPosts;
				downOffset = totalPosts;
			}
			
			// Destroy old waypoints.
			$('.post-permalink').waypoint('destroy');
			$('.post-footer').waypoint('destroy');
			
			// Disable the scrollUp waypoint.
			$('.load-more-posts-up').waypoint('disable');
			
			// If loading more posts scrolling down.
			$.ajax({
				url: HTTP_SERVER + 'forums',
				type: 'POST',
				datatype: 'json',
				data: 'appRequest=' + encodeURIComponent(JSON.stringify({
					type         : 'forum',
					action       : 'load-more-posts-down',
					forumId      : $('#forum-id').val(),
					topicId      : $('#topic-id').val(),
					topicAlias   : $('#topic-alias').val(),
					limit        : downLimit,
					offset       : downOffset,
					numOfPosts   : numOfPosts,
					bottomPostId : bottomPostId,
					csrfToken    : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					var response = JSON.parse(data)
					  , waypointsList
					  , lastWaypoint
					  , lastWaypointId
					  , scrollTop;
					
					addPosts(response.posts, response.topicAlias, response.modPerms, 'down', response, function() {
						
						$('#current-post').addClass('rebinding');

						// Rebind waypoints
						bindPostWaypointPermalink();
						bindPostWaypointFooter();
						
						$.waypoints('refresh');
						
						$('#current-post').removeClass('rebinding');
						
						// Revise the number of posts and current offset.
						numOfPosts = parseInt(numOfPosts) + parseInt(response.limit);
						offset = parseInt(offset) + parseInt(response.limit);
						
						if(typeof(response.noMorePostsDown) !== 'undefined') {
							noMorePostsDown = true;
						}
						
						// Fade the loading indicator.
						window.setTimeout(function() {
							$('#posts-loading').fadeOut();
							// Enable the scrollUp waypoint.
							$('.load-more-posts-up').waypoint('enable');
						}, 2000);
						
					});
					
					return false;
				}
			});
		} else if(!autoloadPosts && direction === "down") {
			// If the post autoloader has been turned off keep the scroller from moving past the bottom of the forum.
			$('#post-scroller').css({
				'position' : 'absolute'
			});
		}
	}, {
		offset : 'bottom-in-view'
	});
}

function addPosts(posts, topicAlias, modPerms, direction, response, callback) {
	var post, postHeight, postIds = {}, viewportScroll, x;
	
	for(x = 0; x < posts.length; x++) {
		
		// Store the ids for later when we want to show each post one a time.
		postIds[x] = posts[x].id;
		
		// Hide Anonymous post count
		if(posts[x].username === 'Anonymous') {
			posts[x].forum_post_count = '';
		}
		
		// Build the post, keep it hidden initially until we want to show it.
		post = 
		'<div id="post-' + posts[x].id + '" class="post-wrap clearfix post-number-' + posts[x].post_order + '">' +
			'<div class="inner clearfix">' +
				'<div class="post clearfix">' +
					'<div class="post-head">' +
						'<span class="post-username">' +
							'<a id="username-for-post-' + posts[x].id + '" href="' + HTTP_SERVER + 'forums/user/' + posts[x].username_alias + '/' + posts[x].user_id + '">' +
								posts[x].username +
							'</a>' +
						'</span>' +
						'<span class="post-permalink">' +
							'<a id="post-permalink-' + posts[x].id + '" href="' + HTTP_SERVER + 'forums/topic/' + topicAlias + '/' +  posts[x].topic_id + '/' + posts[x].id + '#" title="Permalink for post ' + posts[x].post_order + '">' +
								'#' + posts[x].post_order + 
							'</a>' +
						'</span>' +
					'</div>' +
					'<div class="main-post">' +
						'<div class="post-author-info">' +
							'<input type="hidden" id="post-' + posts[x].id + '-userid" value="' + posts[x].user_id + '" />' +
							'<p class="post-role">' +
								response.highest_ranked_role[posts[x].id] +
							'</p>' +
							'<div id="user-avatar-' + posts[x].id + '" class="user-avatar">' +

							'</div>' +
							'<p id="user-post-count-' + posts[x].id + '" class="post-user-post-count">' +
								'<span class="post-count-for-' + posts[x].user_id + '">' +
									posts[x].forum_post_count + ' ' +
								'</span>' +
								'<span class="post-count-label-for-' + posts[x].user_id + '">' +
									posts[x].post_count_label +
								'</span>' +
							'</p>' +
						'</div>' +
						'<div class="post-body clearfix">' +
							'<p id="posted-info-' + posts[x].id + '" class="posted-info">' +
								'Posted ' + posts[x].date_posted +
							'</p>' +
							'<div id="post-content-' + posts[x].id +'" class="post-content">' +
								posts[x].post_content +
							'</div>' +
						'</div>' +
					'</div>' +
					'<div id="post-footer-' + posts[x].id + '" class="post-footer clearfix">' +
						'<ul id="post-menu-' + posts[x].id + '" class="post-menu">' +

						'</ul>' +
					'</div>' +
				'</div>' +
			'</div>' +
		'</div>';
		
		// Append/Prepend post.
		if(direction === 'down' && $('#post-' + posts[x].id).length === 0) {
			$('#forum-posts').append(post);
		} else if(direction === 'up' && $('#post-' + posts[x].id).length === 0) {
			$('#forum-posts').prepend(post);
			viewportScroll = $(window).scrollTop();
			postHeight = $('#post-' + posts[x].id).height();
			//console.log('Viewport: ' + viewportScroll);
			//console.log('Post Height:' + postHeight);
			// Adjust viewport
			$(window).scrollTop(viewportScroll + postHeight + 40);
			//console.log($(window).scrollTop());
		}
		
		// Add user avatar
		if(typeof(posts[x].avatar) !== "undefined" && posts[x].avatar) {
			$("#user-avatar-" + posts[x].id).append(
				'<div class="avatar">' +
					'<img src="' + HTTP_AVATARS + posts[x].avatar + '" alt="' + posts[x].username + '" title="' + posts[x].username +'" />' +
				'</div>'
			);
		} else if(typeof(defaultAvatar) !== "undefined" && defaultAvatar) {
			//console.log(defaultAvatar);
			$("#user-avatar-" + posts[x].id).append(
				'<div class="avatar">' +
					'<img src="' + defaultAvatar + '" alt="' + posts[x].username + '" title="' + posts[x].username + '" />' +
				'</div>'
			);
		} else {
			$("#user-avatar-" + posts[x].id).append(
				'<div class="no-avatar">' +
					'<div class="inner">' +
						'<span>No Avatar</span>' +
					'</div>' +
				'</div>'
			);
		}
		
		//console.log(modPerms);
		
		// Add moderator permissions if allowed
		if(typeof(modPerms) !== 'undefined' && modPerms[posts[x].id]['delete']) {
			$('#post-menu-' + posts[x].id).append(
				'<li id="delete-post-' + posts[x].id + '" class="post-menu-delete">' +
					'Delete' +
				'</li>'
			);
		}
		if(typeof(modPerms) !== 'undefined' && modPerms[posts[x].id]['edit']) {
			$('#post-menu-' + posts[x].id).append(
				'<li id="edit-post-' + posts[x].id + '" class="post-menu-edit">' +
					'Edit' +
				'</li>'
			);
		}
		
		// Add member post options
		if(typeof(username) !== 'undefined') {
			$('#post-menu-' + posts[x].id).append(
				'<li id="quote-post-' + posts[x].id + '" class="post-menu-quote">' +
					'Quote' +
				'</li>' +
				'<li id="report-post-' + posts[x].id + '" class="post-menu-report">' +
					'Report' +
				'</li>'
			);
		}
		
		// Check for best answer
		// If best answer isn't selected and the user is the topic starter
		// If the post is not marked as best answer and the user is the topic starter and the topic starter can mark best answer or the user has the moderator ability to mark best answers for this forum.
		if(posts[x].best_answer != 1 && (typeof(userId) !== "undefined" && userId === topic.user_id && forum.best_answer == 1 && forum.topic_starter_best_answer == 1) || (typeof(modPerms) !== 'undefined' && modPerms[posts[x].id]['toggle_answered'])) {
			// Do not allow the first post to be selected as the best answer.
			if(parseInt(posts[x].post_order) !== 1) {
				if($("#best-answer-post-" + posts[x].id).length === 0) {
					$("#quote-post-" + posts[x].id).after(
						'<li id="best-answer-post-' + posts[x].id + '" class="post-menu-best-answer">' +
							'<span class="ui-icon ui-icon-check"></span>' +
							'<span>Best Answer</span>' +
						'</li>'
					);
				}
			}
		}
		
		// If the best answer was selected show it
		if(posts[x].best_answer == 1) {
			$("#posted-info-" + posts[x].id).after(
				'<p class="best-answer">' +
					'<span class="ui-icon ui-icon-check"></span>' +
					'<span>Best Answer</span>' +
				'</p>'
			);
		}
	}
	
	callback(response);
}

function setPostScrollerProgress(postNumber) {
	
	var totalPosts;
	
	totalPosts = parseInt($('#total-posts').text(), 10);
	
	// Set the background color to show the progress.
	$('#post-scroller > .inner > .bkgd').css({
		'border-right-width' : '1px',
		width: postNumber / totalPosts * 100 + '%'
	});
}

function paginateTopics() {
	$(".topic-row").each(function() {
		var id = $(this).prop("id").replace("topic-", "")
		  , alias = $(this).attr("data-alias")
		  , numOfPosts = parseInt($(this).attr("data-num-of-posts"), 10)
		  , paginated = $(this).attr("data-paginated")
		  , numOfPages = 0
		  , maxPages = 10
		  , pager
		  , x;
		
		//console.log(id);
		//console.log(paginated);
		
		if(paginated === 'false') {
			numOfPages = Math.ceil(numOfPosts / RB.forum.postsPerPage);
			
			if(numOfPages === 1) {
				return;
			}
			
			pager = '<ul class="topic-pager">';
			// Iterate through each page and build it
			for(x = 1; x <= numOfPages; x++) {
				
				if(x < maxPages && x < numOfPages - 3) {
					pager += '<li class="topic-page">' +
								'<a href="' + HTTP_FORUM + 'topic/' + alias + '/' + id + '/page-' + x + '">' +
									x
								'</a>' +
							 '<li>'
				} else if(x >= numOfPages - 3) {
					if(x === numOfPages - 3 && numOfPages > 10) {
						pager += '<li class="topic-page dots">' +
									'...'
								 '</li>'
					} else {
						pager += '<li class="topic-page">' +
									'<a href="' + HTTP_FORUM + 'topic/' + alias + '/' + id + '/page-' + x + '">' +
										x
									'</a>' +
								 '<li>'
					}
				}
			}
			
			pager += '</ul>';
			
			// Append the pager to the topic
			$("#topic-title-" + id).append(pager);
			
			// Unset the pager.
			pager = '';
			
			// Mark the topic has paginated
			$(this).attr("data-paginated", "true");
		}
	});
}

function setActiveUsers(activeUsers) {
	var usersOnline = '', x;
	
	for(x = 0; x < activeUsers.length; x++) {
		usersOnline += 
		'<span>' +
			'<a href="' + HTTP_FORUM + 'user/' + activeUsers[x].usernameAlias + '/' + activeUsers[x].userId + '">' +
				activeUsers[x].username +
			'</a>'
		if(x < activeUsers.length - 1) {
			usersOnline += ', ';
		}
		
		usersOnline += '</span>';
	}
	
	//console.log(usersOnline);

	$("#users-online").html(
		usersOnline
	);
}

function buildPoll(poll) {
	//console.log(poll);
	var choice, totalVotes = 0, percentOfVote, votes;
	
	// Clear existing poll data
	$('#forum-poll-choices, #forum-poll-results').html('');
	
	// Set the poll id
	$('#poll-id').val(poll[0]['poll_id']);
	
	// Set the poll title
	$('#forum-poll-title').text(poll[0]['title']);
	
	for(choice in poll) {
		totalVotes += parseInt(poll[choice].votes, 10);
	}
	
	for(choice in poll) {
		$('#forum-poll-choices').append(
			'<li>' +
				'<label class="center-label">' +
					'<input id="choice-' + poll[choice].id + '" type="radio" class="poll-choice center-toggle" name="pollChoice[]" value="' + poll[choice].id + '" />' +
					poll[choice].choice +
				'</label>' +
			'</li>'
		);
		
		votes = parseInt(poll[choice].votes, 10);
		
		if(totalVotes === 0) {
			percentOfVote = 0;
		} else {
			percentOfVote = parseFloat(votes / totalVotes).toFixed(2); 
		}
		
		percentOfVote *= 100;
		
		$('#forum-poll-results').append(
			'<li>' +
				'<div class="poll-result-choice" data-votes="' + votes + '">' +
					poll[choice].choice +
					'<span class="votes-for-choice">' +
						'( ' + poll[choice].votes + ' votes [' + percentOfVote + '%] )' +
					'</span>' +
				'</div>' +
				'<div class="progress-bar topic-poll">' +
					'<span style="width: ' + percentOfVote + '%;"></span>' +
				'</div>' +
			'</li>'
		);
	}
	
	$('#forum-poll').show();
}
