jQuery(document).ready(function ($) {
	function getTourEmbedUrl(value) {
		const iframeMatch = value.match(/<iframe[^>]+src\s*=\s*["']([^"']+)["']/i);
		const rawUrl = (iframeMatch ? iframeMatch[1] : value).trim();

		try {
			const url = new URL(rawUrl);
			const host = url.hostname.toLowerCase();
			const hostMatches = (domain) => host === domain || host.endsWith(`.${domain}`);

			if (hostMatches('youtu.be')) {
				return `https://www.youtube.com/embed/${url.pathname.split('/').filter(Boolean)[0]}`;
			}

			if (hostMatches('youtube.com') || hostMatches('youtube-nocookie.com')) {
				const pathMatch = url.pathname.match(/\/(?:embed|shorts)\/([^/]+)/);
				const videoId = url.searchParams.get('v') || (pathMatch && pathMatch[1]);
				return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
			}

			if (hostMatches('vimeo.com')) {
				const videoId = url.pathname.match(/\/(?:video\/)?(\d+)\/?$/);
				return videoId ? `https://player.vimeo.com/video/${videoId[1]}` : '';
			}

			if (host === 'drive.google.com') {
				return url.href.replace(/\/view(?:\?.*)?$/, '/preview');
			}

			return ['http:', 'https:'].includes(url.protocol) ? url.href : '';
		} catch (error) {
			return '';
		}
	}

	function updateTourPreview() {
		const tourInput = $('input#tour');
		const embedUrl = getTourEmbedUrl(tourInput.val());
		const oembedContainer = $('#tour-preview');
		oembedContainer.empty();

		if (embedUrl) {
			oembedContainer.append(
				$('<iframe>', {
					allow: 'autoplay; encrypted-media; fullscreen; picture-in-picture; xr-spatial-tracking',
					allowfullscreen: true,
					loading: 'lazy',
					src: embedUrl,
					title: 'Manual video or tour preview',
				})
			);
		}
	}

	$('input#tour').on('input', updateTourPreview);
	updateTourPreview();
});
