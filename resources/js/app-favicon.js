// PATH FILE: resources/js/app-favicon.js

(function () {
    const iconPath = '/images/icon.png';

    function setFavicon(rel, sizes = null) {
        let link = document.querySelector(`link[rel="${rel}"]`);

        if (!link) {
            link = document.createElement('link');
            link.setAttribute('rel', rel);
            document.head.appendChild(link);
        }

        link.setAttribute('type', 'image/png');
        link.setAttribute('href', iconPath);

        if (sizes) {
            link.setAttribute('sizes', sizes);
        }
    }

    setFavicon('icon', '32x32');
    setFavicon('shortcut icon', '32x32');
    setFavicon('apple-touch-icon', '180x180');
})();
