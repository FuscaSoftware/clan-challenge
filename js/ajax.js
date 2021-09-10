/**
 * General JavaScript to Handle Ajax Data, Calls and Responses
 * @author sebastian.braun@fusca.de
 * @version 2.0 (2017-10-31) changed 2021-04-12
 * uses nprogress.js
 * uses jquery
 */
/**
 * @deprecated
 * @type {number}
 */
var fadeTime = 400;

var fuscaAjax = {
	fadeTime: 400,
};

$(document).ajaxStart(function () {
	NProgress.start();
});

$(document).ajaxComplete(function () {
	NProgress.done();
});

$.postJSON = function (url, data, func) {
	$.post(url, data, func, 'json');
};

/**
 * @author Sebastian & Philip
 * @param {element or selector} form
 * @returns {getDataObjectByForm.data}
 */
function getDataObjectByForm(form) {
	form = $(form);
	let data = {
		request_type: 'ajax',
		date: new Date().valueOf() /* to avoid request caching */
	};
	/* do not use browser cache */
	if (form.data('nocache') !== undefined && form.data('nocache')) {
		data.date = data.date;
	} else { /* allow browser cache */
		data.date = "";
		// delete data.date;
	}
	let fn_collect = function(container) {
		$(':input', container).each(function (index, element) {
				element = $(element);
				if (element.attr('name') !== undefined) {
					if (element.attr('type') == 'checkbox') {
						if (element.is(':checked')) {
							data[element.attr('name')] = element.val();
						} else {
							data[element.attr('name')] = 0;
						}
					} else if (element.attr('type') == 'radio') {
						if (element.is(':checked')) {
							data[element.attr('name')] = element.val();
						}
					} else {
						data[element.attr('name')] = element.val();
					}
				}
			}
		);
	};
	if (form.data('additional_fields') !== undefined) {
		let container2 = $(form.data('additional_fields'));
		fn_collect(container2);
		/*
		console.log(container2);
		for (var i = 0; i < $(':input', container2).length; i++) {
			// console.log(container2);
			// console.log(container2);
			data[$($(':input', container2)[i]).attr("name")] = $($(':input', container2)[i]).val();
		}
		*/
	}
	if (form.data('additional_fields2') !== undefined) {
		let container3 = $(form.data('additional_fields2'));
		fn_collect(container3);
		/*
		for (var i = 0; i < $(':input', container3).length; i++) {
			data[$($(':input', container3)[i]).attr("name")] = $($(':input', container3)[i]).val();
		}
		 */
	}
	if (form.data('limit_from') !== undefined)
		data.limit_from = form.data('limit_from');
	if (form.data('limit_num') !== undefined)
		data.limit_num = form.data('limit_num');
	// console.log($(':input', form).length);
	// var t1 = performance.now();
	// A //1200ms
	// for (var i = 0; i < $(':input', form).length; i++) {
	//     if ($($(':input', form)[i]).attr('name') !== undefined)
	//         data[$($(':input', form)[i]).attr('name')] = $($(':input', form)[i]).val();
	// }
	// B //800ms
	// var inputs_count = $(':input', form).length;
	// for (var i = 0; i < inputs_count; i++) {
	//     if ($($(':input', form)[i]).attr('name') !== undefined)
	//         data[$($(':input', form)[i]).attr('name')] = $($(':input', form)[i]).val();
	// }
	// C //5ms
	/*
	if (0) {
	$(':input', form).each(function (index, element) {
			element = $(element);
			if (element.attr('name') !== undefined) {
				if (element.attr('type') == 'checkbox') {
					if (element.is(':checked')) {
						data[element.attr('name')] = element.val();
					} else {
						data[element.attr('name')] = 0;
					}
				} else if (element.attr('type') == 'radio') {
					if (element.is(':checked')) {
						data[element.attr('name')] = element.val();
					}
				} else {
					data[element.attr('name')] = element.val();
				}
			}
		}
	);
	}*/
	fn_collect(form);
	//
	return data;
}

/**
 * Handles an Ajax-Json-Response to modify the client's DOM to change the view.
 * e.g. (server-side) json_encode([html => append [ div.main => "html to append" ]])
 *
 * @param json
 */
function jsonToDom(json) {
	// var selector, element;
	var element;
	var time = 50;
	var i = 0;
	var checkSelector = (selector) => {
		if ($(selector).length === 0)
			console.log("invalid selector: " + selector);
	};
	if (json.html !== undefined) {

		if (json.html.append !== undefined) {
			for (var selector in json.html.append) {
				element = $(json.html.append[selector]).hide();
				$(selector).delay(i++ * time).append(element);
				/* jshint loopfunc:true */
				element.fadeIn(function () {
					$(this).removeClass('is-not-faded');
					if (json.html.callback !== undefined) {
						var callbackFunction = window[json.html.callback];
						callbackFunction();
					}
				});
			}
		}
		if (json.html.appendTo !== undefined) {
			for (var selector in json.html.appendTo) {
				checkSelector(selector);
				element = $(json.html.appendTo[selector]).hide();
				$(selector).appendTo(element);
				element.fadeIn();
			}
		}
		if (json.html.prepend !== undefined) {
			for (var selector in json.html.prepend) {
				checkSelector(selector);
				element = $(json.html.prepend[selector]).hide();
				$(selector).delay(i++ * time).prepend(element);
				element.fadeIn();
			}
		}
		if (json.html.insertAfter !== undefined) {
			for (var selector in json.html.insertAfter) {
				checkSelector(selector);
				element = $(json.html.insertAfter[selector]).hide();
				console.log(element);
				$(element).delay(i++ * time).insertAfter(selector);
				/* jshint loopfunc:true */
				element.fadeIn(function () {
					$(this).removeClass('is-not-faded');
					if (json.html.callback !== undefined) {
						var callbackFunction = window[json.html.callback];
						callbackFunction();
					}
				});
			}
		}
		if (json.html.insertBefore !== undefined) {
			for (var selector in json.html.insertBefore) {
				checkSelector(selector);
				element = $(json.html.insertBefore[selector]).hide();
				$(element).delay(i++ * time).insertBefore(selector);
				/* jshint loopfunc:true */
				element.fadeIn(function () {
					$(this).removeClass('is-not-faded');
					if (json.html.callback !== undefined) {
						var callbackFunction = window[json.html.callback];
						callbackFunction();
					}
				});
			}
		}
		if (json.html.replace !== undefined) {
			for (var selectorReplace in json.html.replace) {
				replace(selectorReplace, json.html.replace[selectorReplace], fadeTime);
			}
		}

		// function replace(selectorReplace, newHtml, fadeTime){
		//     var newEl = $(newHtml).fadeTo(0, 0.01);
		//     $(selectorReplace).fadeTo(fadeTime, 0.01, function(){
		//         newEl.replaceAll(selectorReplace).delay(0).fadeTo(fadeTime, 1);
		//     });
		// }

		/* bug: new outer-html gets lost */
		if (json.html.html !== undefined) {
			for (var selector in json.html.html) {
				checkSelector(selector);
				try {
					// var new_html = $(json.html.html[selector]).fadeTo(0, 0.01).html();
					var new_html = $(json.html.html[selector]).fadeTo(0, 0.01).html();
				} catch (e) {
					if (new_html == undefined)
						alert("Html aus Antwort kann nicht verarbeitet werden. Kein Html-Tag?");
					// console.log(e);
				}
				$(selector).fadeTo(fadeTime, 0.01, function () {
					$(selector).delay(i++ * time + 1).html(new_html).fadeTo(fadeTime, 1, function () {
						console.log('finished');
					});
					// $(selector).html(new_html).fadeTo(fadeTime, 1, function(){
					//     console.log('finished');
					// });
				});
			}
		}
		/* with bugfix */
		if (json.html.html2 !== undefined) {
			for (let selector_html2 in json.html.html2) {
				checkSelector(selector_html2);
				html2(selector_html2, json.html.html2[selector_html2], fadeTime, i, time);
			}
		}

		if (json.html.fadeIn !== undefined) {
			for (var selector in json.html.fadeIn) {
				checkSelector(selector);
				$(selector).delay(i++ * time).fadeIn();
			}
		}
		if (json.html.fadeOut !== undefined) {
			for (var selector in json.html.fadeOut) {
				checkSelector(selector);
				$(selector).delay(i++ * time).fadeOut();
			}
		}
		if (json.html.show !== undefined) {
			for (var selector in json.html.show) {
				checkSelector(selector);
				$(selector).delay(i++ * time).show();
			}
		}
		if (json.html.hide !== undefined) {
			for (var selector in json.html.hide) {
				checkSelector(selector);
				$(selector).delay(i++ * time).hide();
			}
		}
		if (json.html.remove !== undefined) {
			for (var selector_remove in json.html.remove) {
				checkSelector(selector_remove);
				$(selector_remove).delay(i++ * time).remove(selector_remove).fadeOut();
			}
		}
		if (json.html.addClass !== undefined) {
			for (var selector in json.html.addClass) {
				checkSelector(selector);
				$(selector).delay(i++ * time).addClass(json.html.addClass[selector]);
			}
		}
		if (json.html.removeClass !== undefined) {
			for (var selector in json.html.removeClass) {
				checkSelector(selector);
				$(selector).delay(i++ * time).removeClass(json.html.removeClass[selector]);
			}
		}
		if (json.html.value !== undefined) {
			for (var selector in json.html.value) {
				checkSelector(selector);
				$(selector).delay(i++ * time).val(json.html.value[selector]);
			}
		}
		/* wtf */
		if (json.html.attribute !== undefined) {
			for (var selectorA in json.html.attribute) {
				// $(selectorA).delay(i++ * time).attr('data-month', json.html.attribute[selectorA]);
				// $(selectorA).delay(i++ * time).data('month', json.html.attribute[selectorA]);
				$(selectorA).delay(i++ * time).attr(json.html.attribute[selectorA].key, json.html.attribute[selectorA].value);
				// console.log(
				//     "Test"
				//     $(selectorA).delay(i++ * time).attr(json.html.attribute[selectorA].key)
				// );
			}
		}
		/* wtf */
		if (json.html.limit_from !== undefined) {
			for (var selector in json.html.limit_from) {
				$(selector).delay(i++ * time).val('limit_from', json.html.limit_from[selector]);
			}
		}

		/* wtf */
		if (json.html.href !== undefined) {
			for (var selector in json.html.href) {
				$(selector).delay(i++ * time).attr("href", json.html.href[selector]);
			}
		}
	}
}


/**
 *
 * @param string form or any DOM-Element(-Selector) which can hold inputs which should be send to the server
 * @returns {boolean}
 */
function ajax_submit(form, fn_success) {
	$("body").css("cursor", "progress");
	var url = $(form).attr("action");
	if (url == undefined) {
		url = $(form).data("action");
	}
	if (url == undefined) {
		url = $(form).attr('href');
	}
	if (url == undefined) {
		console.log("selector/url '" + form + "' invalid");
		return false;
	}
	data = getDataObjectByForm(form);
	return ajax_data2(url, data, fn_success, 0, "ajax");
}

function ajax_data(url, data) {
	return ajax_data2(url, data, null, 0, "ajax");
}

/**
 * requests a url with json-data to server and run success
 * @param string url to request
 * @param object data to send to server
 * @param function success(return/json_from_server)
 * @param int cache if 0 a date is appended
 * @param string request_type: ajax|post|
 */
function ajax_data2(url, data, success, cache, request_type) {
	if (data.request_type === undefined && (request_type === undefined))
		data.request_type = "ajax";
	if (data.date === undefined && (!cache || cache === undefined || cache === 0))
		data.date = new Date().valueOf();//to avoid request caching


	$("body").css("cursor", "progress");
	var doneFn = function (response) {
		if (response instanceof Object)
			var json = response;
		else
			var json = $.parseJSON(response);
		// console.log(response);
		// console.log(json);
		// $('.panel-debug').fadeOut();
		// $('.panel-debug .panel-body .error_messages iframe').contents().find('body').html('');
		jsonToDom(json);
		if (success === undefined || success === null) {
		} else if (typeof success == "function") {
			success(json);
		} else {
			console.log("success ist not a valid function" + typeof success);
		}
		if (json.reload !== undefined && json.reload)
			location.reload();
		if (json.location !== undefined && json.location)
			location.href = json.location;
		$("body").delay(1000).css("cursor", "default");/* trotzdem immer zu früh!? */
	};
	var failFn = function (jqxhr, textStatus, error) {
		var err = textStatus + ", " + error;
		console.log("Request Failed: " + err);
		console.log("size of data: " + size);
		// console.log("jqxhr: " + jqxhr);
		console.log(jqxhr);
		// $('.panel-debug .panel-body .error_messages').html(jqxhr.responseText);
		$('.panel-debug .panel-body .error_messages iframe').contents().find('body').html(jqxhr.responseText);
		$('.panel-debug').fadeIn();
		console.log(textStatus);
		console.log(error);
		if (jqxhr.status !== 401)
			alert("Fehler!\nBei Anfrage: " + url + "\nBitte wenden Sie sich an das Support-Team!");
		else
			doneFn(jqxhr.responseJSON);
	};
	var size = JSON.stringify(data).length;
	if (request_type == "post" || size > 3072) {
		/* it seems post-requests are not cached by firefox */
		var jqxhr = $.post(url, data, function () {
		}, 'json')
			.done(doneFn)
			.fail(failFn);
	} else {
		var jqxhr = $.get(url, data, function () {
		}, 'json')
			.done(doneFn)
			.fail(failFn);
	}
	return false;
}

function replace(selectorReplace, newHtml, fadeTime) {
	// var newEl = $(json.html.replace[selectorReplace]).fadeTo(0, 0.01);
	// $(selectorReplace).fadeTo(fadeTime, 0.01, function(){
	//     newEl.replaceAll(selectorReplace).delay(0).fadeTo(fadeTime, 1);
	// });
	var newEl = $(newHtml).fadeTo(0, 0.01);
	$(selectorReplace).fadeTo(fadeTime, 0.01, function () {
		newEl.replaceAll(selectorReplace).delay(0).fadeTo(fadeTime, 1);
	});
}

function html2(selector_html2, content, fadeTime, i, time) {
	let new_html;
	try {
		// var new_html = $(json.html.html2[selector]).fadeTo(0, 0.01).html();//only innerHTML
		// var new_html = $(json.html.html2[selector]).fadeTo(0, 0.01)[0].outerHTML;
		// var new_html = $(json.html.html2[selector_html2]).wrapAll('<temp>').parent().fadeTo(0, 0.01).html();
		new_html = $(content).wrapAll('<temp>').parent().fadeTo(0, 0.01).html();
		// var new_obj = $(json.html.html2[selector]).fadeTo(0, 0.01);
		// var new_html = new_obj.wrapAll('<div>').parent().html();
		// var new_html0 = $(json.html.html2[selector]).fadeTo(0, 0.01);
		// var new_html = $('<div>').append($(new_html0).clone()).html();
	} catch (e) {
		if (new_html == undefined) {
			alert("Html aus Antwort kann nicht verarbeitet werden. Kein Html-Tag?");
			console.log(new_obj);
			console.log(new_html);
		}
		// console.log(e);
	}
	$(selector_html2).fadeTo(fadeTime, 0.01, function () {
		$(selector_html2).delay(i++ * time + 1).html(new_html).fadeTo(fadeTime, 1, function () {
			console.log(selector_html2 + ' html2() finished');
		});
		// $(selector).html(new_html).fadeTo(fadeTime, 1, function(){
		//     console.log('finished');
		// });
	});
}

/**
 * for testing
 * @param el
 * @param event
 */
function onKeyUp(el, event) {
	console.log(el);
	console.log(event);
	console.log(this);
}

/**
 * Could be used in an input as onKeyUp-Attribute to call the given function
 * @param element
 * @param event
 * @param fn
 * @param params
 * @returns {boolean}
 */
function onEnter(element, event, fn, params) {
	if (event.keyCode === 13) {
		fn(params);
	}
	return false;
}
