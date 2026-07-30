(function () {
	'use strict';

	function initPropertyEditor(editor) {
		var tabs = Array.from(editor.querySelectorAll('[data-rf-property-tab]'));
		var panels = Array.from(editor.querySelectorAll('[data-rf-property-panel]'));
		var userId = editor.getAttribute('data-user-id') || '0';
		var storageKey = 'rentfetchEditor.activeTab.user.' + userId;
		var tabIds = tabs.map(function (tab) {
			return tab.getAttribute('data-rf-property-tab');
		});

		if (!tabs.length || !panels.length) {
			return;
		}

		function getHashTab() {
			var hash = window.location.hash.replace(/^#/, '');

			try {
				hash = decodeURIComponent(hash);
			} catch (error) {
				return '';
			}

			return tabIds.indexOf(hash) !== -1 ? hash : '';
		}

		function getStoredTab() {
			try {
				var storedTab = window.localStorage.getItem(storageKey);
				return tabIds.indexOf(storedTab) !== -1 ? storedTab : '';
			} catch (error) {
				return '';
			}
		}

		function storeTab(tabId) {
			try {
				window.localStorage.setItem(storageKey, tabId);
			} catch (error) {
				// Storage can be unavailable in privacy-restricted browsers.
			}
		}

		function updateHash(tabId) {
			var nextUrl =
				window.location.pathname +
				window.location.search +
				'#' +
				encodeURIComponent(tabId);

			window.history.replaceState(null, '', nextUrl);
		}

		function refreshPanel(panel) {
			panel.querySelectorAll('.CodeMirror').forEach(function (codeMirrorElement) {
				if (codeMirrorElement.CodeMirror) {
					codeMirrorElement.CodeMirror.refresh();
				}
			});

			if (window.tinymce) {
				panel.querySelectorAll('textarea.wp-editor-area').forEach(function (textarea) {
					var tinyMceEditor = window.tinymce.get(textarea.id);

					if (tinyMceEditor) {
						tinyMceEditor.fire('ResizeEditor');
					}
				});
			}

			window.dispatchEvent(new Event('resize'));
		}

		function dispatchTabEvent(name, tabId, panel) {
			document.dispatchEvent(
				new CustomEvent(name, {
					detail: {
						tabId: tabId,
						panel: panel,
					},
				})
			);
		}

		function initRichEditors(panel) {
			if (!window.wp || !wp.editor || !wp.editor.initialize) {
				return;
			}

			panel
				.querySelectorAll(
					'textarea[data-rf-rich-editor]:not([data-rf-rich-editor-initialized])'
				)
				.forEach(function (textarea) {
					if (textarea.disabled) {
						textarea.setAttribute(
							'data-rf-rich-editor-initialized',
							'disabled'
						);
						return;
					}

					textarea.setAttribute(
						'data-rf-rich-editor-initialized',
						'true'
					);

					wp.editor.initialize(textarea.id, {
						mediaButtons: false,
						quicktags: true,
						tinymce: {
							wpautop: true,
						},
					});
				});
		}

		function loadLazyFragment(fragment, tabId, panel) {
			if (
				'loaded' === fragment.getAttribute('data-rf-lazy-state') ||
				'loading' === fragment.getAttribute('data-rf-lazy-state')
			) {
				return;
			}

			if (
				'undefined' === typeof rentfetchPropertyEditor ||
				!rentfetchPropertyEditor.ajaxUrl
			) {
				return;
			}

			fragment.setAttribute('data-rf-lazy-state', 'loading');

			var body = new window.URLSearchParams();
			body.set(
				'action',
				rentfetchPropertyEditor.action ||
					'rentfetch_get_property_editor_fragment'
			);
			body.set('nonce', rentfetchPropertyEditor.nonce);
			body.set(
				'fragment',
				fragment.getAttribute('data-rf-lazy-fragment') || ''
			);
			body.set('post_id', fragment.getAttribute('data-post-id') || '0');

			window
				.fetch(rentfetchPropertyEditor.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						'Content-Type':
							'application/x-www-form-urlencoded; charset=UTF-8',
					},
					body: body.toString(),
				})
				.then(function (response) {
					if (!response.ok) {
						throw new Error('Request failed');
					}
					return response.json();
				})
				.then(function (response) {
					if (
						!response ||
						!response.success ||
						!response.data ||
						'string' !== typeof response.data.html
					) {
						throw new Error('Invalid response');
					}

					fragment.innerHTML = response.data.html;
					fragment.setAttribute('data-rf-lazy-state', 'loaded');
					dispatchTabEvent(
						'rentfetch:property-tab-content-loaded',
						tabId,
						panel
					);

					window.requestAnimationFrame(function () {
						refreshPanel(panel);
					});
				})
				.catch(function () {
					fragment.setAttribute('data-rf-lazy-state', 'error');
					fragment.innerHTML =
						'<div class="notice notice-error inline"><p>That content could not be loaded. <button type="button" class="button-link" data-rf-lazy-retry>Try again</button></p></div>';

					var retry = fragment.querySelector(
						'[data-rf-lazy-retry]'
					);
					if (retry) {
						retry.addEventListener('click', function () {
							fragment.removeAttribute('data-rf-lazy-state');
							fragment.innerHTML =
								'<p class="rf-property-editor-loading" role="status">Loading…</p>';
							loadLazyFragment(fragment, tabId, panel);
						});
					}
				});
		}

		function preparePanel(tabId, panel) {
			initRichEditors(panel);
			dispatchTabEvent(
				'rentfetch:property-tab-activated',
				tabId,
				panel
			);

			panel
				.querySelectorAll('[data-rf-lazy-fragment]')
				.forEach(function (fragment) {
					loadLazyFragment(fragment, tabId, panel);
				});
		}

		function activateTab(tabId, options) {
			var settings = options || {};
			var activeTab = tabs.find(function (tab) {
				return tab.getAttribute('data-rf-property-tab') === tabId;
			});
			var activePanel = panels.find(function (panel) {
				return panel.getAttribute('data-rf-property-panel') === tabId;
			});

			if (!activeTab || !activePanel) {
				return;
			}

			tabs.forEach(function (tab) {
				var isActive = tab === activeTab;
				tab.classList.toggle('is-active', isActive);
				tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
				tab.setAttribute('tabindex', isActive ? '0' : '-1');
			});

			panels.forEach(function (panel) {
				var isActive = panel === activePanel;
				panel.classList.toggle('is-active', isActive);
				panel.hidden = !isActive;
			});

			if (settings.persist !== false) {
				storeTab(tabId);
			}

			if (settings.updateHash !== false) {
				updateHash(tabId);
			}

			if (settings.focus) {
				activeTab.focus();
			}

			if (settings.scrollTab) {
				activeTab.scrollIntoView({
					behavior: 'smooth',
					block: 'nearest',
					inline: 'nearest',
				});
			}

			preparePanel(tabId, activePanel);

			window.requestAnimationFrame(function () {
				refreshPanel(activePanel);
			});
		}

		tabs.forEach(function (tab, index) {
			tab.addEventListener('click', function () {
				activateTab(tab.getAttribute('data-rf-property-tab'), {
					scrollTab: true,
				});
			});

			tab.addEventListener('keydown', function (event) {
				var nextIndex = index;

				switch (event.key) {
					case 'ArrowLeft':
						nextIndex = (index - 1 + tabs.length) % tabs.length;
						break;
					case 'ArrowRight':
						nextIndex = (index + 1) % tabs.length;
						break;
					case 'Home':
						nextIndex = 0;
						break;
					case 'End':
						nextIndex = tabs.length - 1;
						break;
					default:
						return;
				}

				event.preventDefault();
				activateTab(tabs[nextIndex].getAttribute('data-rf-property-tab'), {
					focus: true,
					scrollTab: true,
				});
			});
		});

		window.addEventListener('hashchange', function () {
			var hashTab = getHashTab();

			if (hashTab) {
				activateTab(hashTab, {
					updateHash: false,
				});
			}
		});

		editor.classList.add('is-ready');
		activateTab(getHashTab() || getStoredTab() || 'overview');
	}

	function init() {
		document.querySelectorAll('[data-rf-property-editor]').forEach(initPropertyEditor);
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
