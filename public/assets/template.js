function isNumber(n) {
	return (typeof n === 'number' && isFinite(n));
}

function fixNumber(n) {
	return isNumber(n) ?
		n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
		: n;
}

function getTpl(selector, obj, callback) {
	var tr = $(selector).html();
	tr = parseTpl(tr, obj, callback);
	return tr;
}

function get(path, obj, callback, fb) {
	fb = (typeof fb == "undefined") ? '$\{' + path + '}' : fb;
	return path.split('.').reduce(function (res, key) {
		res = (typeof res[key] == "undefined") ? fb : res[key];
		if (callback && (typeof callback == "function")) {
			res = callback(res);
		}
		return res;
	}, obj);
}

function parseTpl(template, map, callback, fallback) {
	return template.replace(/\$\{.+?}/g, function (match) {
		var path = match.slice(2, -1).trim();
		return get(path, map, callback, fallback);
	});
}

function objsTpl(template, objNames, master, callback) {
	var res = '';
	var obj = {};
	var map = objNames.split(',');
	map.forEach(function (name) {
		name = name.trim();
		obj[name] = master[name];
		res = parseTpl(template, obj, callback);
	});
	return res;
}

function loopTpl(template, map, objName, callback) {
	var res = '';
	var obj = {};
	map.forEach(function (objTpl) {
		obj[objName] = objTpl;
		res += parseTpl(template, obj, callback);
	});
	return res;
}

$(function () {

	var table_height = 0;
	var pad_b;
	// var default_margin_b;
	var num_page = 1;
	var num_table = 1;
	var id_page = '#page' + num_page;
	var id_table = '#table' + num_page;
	var header, thead, footer, wrapper;
	var tva_detail = true;

	$.getJSON('assets/datajson.json')
		.done(function (data) {

			var data_order = $(id_page).attr('data-order');
			var data_replace = $('#tva-tpl').attr('data-replace');
			var parent_replace = $('#tva-tpl').attr('data-parent');

			if (data_order) {
				var obj_before;
				data_order = data_order.split(',');
				data_order.forEach(function (tpl) {
					tpl = tpl.trim();

					var tpl_data = $('#' + tpl).html();
					var parent = $('#' + tpl).attr('data-parent');
					if (!wrapper)
						wrapper = $('#' + tpl).attr('data-wrapper');

					tpl_data = parseTpl(tpl_data, data, fixNumber);
					obj = $(tpl_data);
					if (tva_detail && data_replace && parent_replace && 
						(tpl === parent_replace.trim())) {
						var tva_replace = obj.find('#' + data_replace.trim());
						if (tva_replace.length) {
							var tva_tpl = $('#tva-tpl').html();
							//var tva_obj = $('#tva-tpl').attr('data-obj').trim();
							tva_tpl = loopTpl(tva_tpl, data.total_tva, 'tva', fixNumber);

							tva_replace.replaceWith(tva_tpl);
						}
					}

					if (parent) {
						$('#' + parent.trim()).append(obj);
						obj_before = '#' + parent.trim();
					}
					else if (obj_before) obj.insertAfter(obj_before);
					else $(id_page).append(obj);

				});
			} else {
				header = $('#header').html();
				header = parseTpl(header, data);

				thead = $('#thead').html();
				wrapper = $('#thead').attr('data-wrapper').trim();

				footer = $('#footer').html();
				footer = parseTpl(footer, data, fixNumber);
				footer = $(footer);


				$('header').append(header);
				$(thead).insertAfter('header');
				$('footer').append(footer);
			}

			var page = $(id_page);
			var table = $(id_table + ' tbody');

			var footer_height = $('footer').outerHeight(true);
			var page_height = page.height() + ((page.outerHeight() - page.height()) / 2);
			var header_height = $('header').outerHeight(true);
			var max_height = page_height - footer_height - header_height;

			var row;
			var tr;
			var tr_node_name;
			var isTranche = false;
			var trancheHTML = '';

			data.lignes.forEach(function (ligne) {
				var row_select = '#row';

				if (ligne.type === 'tranche_debut') {
					isTranche = true;
					trancheHTML = getTpl('#tranche-debut', { ligne: ligne }, fixNumber);
					return;
				}
				else if (ligne.type === 'tranche_fin') {
					row_select = '#tranche-fin';
				}

				row = getTpl(row_select, { ligne: ligne }, fixNumber);
				tr_node_name = $(row)[0].nodeName;

				table_height = table.append(trancheHTML + row).outerHeight(true);

				if (table_height > max_height) {
					tr = $(id_table + ' ' + tr_node_name + ':last-child').detach();
					if (isTranche === true) {
						var tr2 = $(id_table + ' ' + tr_node_name + ':last-child').detach();
						tr = tr2.add(tr);
					}
					table_height = table.outerHeight(true);

					pad_b = parseFloat($(id_table + ' ' + tr_node_name + ':last-child td')
						.first().css('padding-bottom'));
					$(id_table + ' ' + tr_node_name + ':last-child td')
						.css('padding-bottom', max_height - table_height + pad_b);

					max_height = page_height - footer_height;
					id_page = 'page' + (++num_page);
					id_table = 'table' + (++num_table);

					$('body').append('<DIV id="' + id_page + '" class="page a4 pagebreak"></DIV>');

					id_page = '#' + id_page;
					
					thead = $('#thead').contents().filter('table').clone();
					page = $(id_page).append(thead.attr('id', id_table));

					id_table = '#' + id_table;
					table = $(id_table + ' tbody');
					table_height = table.append(tr).outerHeight(true);

					$('footer').slice(0, 1).clone().appendTo(id_page);
				}
				if (ligne.type === 'standard') {
					isTranche = false;
					trancheHTML = '';
				}

			});

			$('footer > p:last-child').each(function (index) {
				$(this).text('page ' + (index + 1) + '/' + num_page);
			});

			if (table_height < max_height) {
				pad_b = parseFloat($(id_table + ' tr:last-child td')
					.first().css('padding-bottom'));
				$(id_table + ' tr:last-child td')
					.css('padding-bottom', max_height - table_height + pad_b);

			}
		});
});// function ()
    // endlang