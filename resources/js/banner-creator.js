/**
 * Banner Creator Helper Functions & Magnifier (Lupe)
 */

export function addBannerOptionGroup() {
    const groups = document.querySelectorAll('.banner-option-group, #BannerOptionGroup');
    if (!groups.length) return;

    const lastGroup = groups[groups.length - 1];
    const clone = lastGroup.cloneNode(true);

    // Reset select fields
    const optionSelect = clone.querySelector('select[name="option_id[]"], #option_id');
    if (optionSelect && optionSelect.options.length > 0) {
        let firstValidIndex = 0;
        for (let i = 0; i < optionSelect.options.length; i++) {
            if (!optionSelect.options[i].disabled) {
                firstValidIndex = i;
                break;
            }
        }
        optionSelect.selectedIndex = firstValidIndex;
    }

    const extraSelect = clone.querySelector('select[name="extra_option[]"], #extra_option');
    if (extraSelect) {
        extraSelect.value = '0';
    }

    // Reset text input
    const textInput = clone.querySelector('input[name="text[]"], #text');
    if (textInput) {
        textInput.value = '';
    }

    // Reset coordinates
    const coordX = clone.querySelector('input[name="coord_x[]"], #coord_x');
    if (coordX) {
        coordX.value = '';
    }

    const coordY = clone.querySelector('input[name="coord_y[]"], #coord_y');
    if (coordY) {
        coordY.value = '';
    }

    // Clean up IDs to avoid duplicate DOM IDs
    clone.removeAttribute('id');
    clone.classList.add('banner-option-group');
    clone.querySelectorAll('[id]').forEach((el) => {
        el.removeAttribute('id');
    });

    const addButton = document.querySelector('#AddOptionGroupButton');
    if (addButton) {
        addButton.before(clone);
    }
}

/**
 * Calculates natural image pixel coordinates from a mouse/touch event.
 */
export function getCoordinatesFromEvent(img, event) {
    if (!img || !img.naturalWidth || !img.naturalHeight || !img.clientWidth || !img.clientHeight) {
        return null;
    }

    const rect = img.getBoundingClientRect();
    const clientX = event.clientX !== undefined ? event.clientX : (event.touches && event.touches[0] ? event.touches[0].clientX : 0);
    const clientY = event.clientY !== undefined ? event.clientY : (event.touches && event.touches[0] ? event.touches[0].clientY : 0);

    const mouseX = Math.max(0, Math.min(rect.width, clientX - rect.left));
    const mouseY = Math.max(0, Math.min(rect.height, clientY - rect.top));

    const scaleX = img.naturalWidth / rect.width;
    const scaleY = img.naturalHeight / rect.height;

    const x = Math.max(0, Math.min(img.naturalWidth - 1, Math.floor(mouseX * scaleX)));
    const y = Math.max(0, Math.min(img.naturalHeight - 1, Math.floor(mouseY * scaleY)));

    return { x, y, mouseX, mouseY, clientX, clientY, rect, scaleX, scaleY };
}

/**
 * Updates coordinate inputs upon clicking on the banner template image.
 *
 * @param {MouseEvent|TouchEvent} [event]
 */
export function imageCoordinates(event) {
    const img = document.getElementById('BannerImage');
    if (!img || !event) return;

    const coords = getCoordinatesFromEvent(img, event);
    if (!coords) return;

    const coordXInput = document.getElementById('coord_x');
    const coordYInput = document.getElementById('coord_y');

    if (coordXInput) coordXInput.value = coords.x;
    if (coordYInput) coordYInput.value = coords.y;
}

/**
 * Initializes the Loupe / Magnifier tool for the banner template.
 */
export function initBannerMagnifier() {
    /** @type {HTMLImageElement|null} */
    const img = document.getElementById('BannerImage');
    if (!img) return;

    // Remove existing magnifier instance if already initialized
    const existingLoupe = document.getElementById('banner-magnifier-lens');
    if (existingLoupe) {
        existingLoupe.remove();
    }

    const size = 160; // Magnifier diameter in px
    const zoom = 4;   // 4x zoom magnification factor

    // Create magnifier container
    const loupe = document.createElement('div');
    loupe.id = 'banner-magnifier-lens';
    Object.assign(loupe.style, {
        position: 'fixed',
        width: `${size}px`,
        height: `${size}px`,
        borderRadius: '50%',
        border: '3px solid #ffffff',
        boxShadow: '0 4px 20px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(0, 0, 0, 0.2)',
        overflow: 'hidden',
        pointerEvents: 'none',
        zIndex: '99999',
        display: 'none',
        backgroundColor: '#1a1a1a',
        transform: 'translate(-50%, -50%)',
    });

    // Create Canvas inside loupe
    const canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;
    Object.assign(canvas.style, {
        width: '100%',
        height: '100%',
        display: 'block',
        imageRendering: 'pixelated',
    });
    loupe.appendChild(canvas);

    // Create pixel coordinate badge inside loupe
    const badge = document.createElement('div');
    badge.id = 'banner-magnifier-coords';
    Object.assign(badge.style, {
        position: 'absolute',
        bottom: '8px',
        left: '50%',
        transform: 'translateX(-50%)',
        backgroundColor: 'rgba(0, 0, 0, 0.8)',
        color: '#ffffff',
        padding: '2px 8px',
        borderRadius: '10px',
        fontFamily: 'monospace',
        fontSize: '11px',
        fontWeight: 'bold',
        whiteSpace: 'nowrap',
        pointerEvents: 'none',
        border: '1px solid rgba(255, 255, 255, 0.2)',
        textShadow: '0 1px 2px rgba(0, 0, 0, 0.8)',
    });
    badge.textContent = 'X: 0 | Y: 0';
    loupe.appendChild(badge);

    document.body.appendChild(loupe);

    const ctx = canvas.getContext('2d');

    function updateLoupe(e) {
        const coords = getCoordinatesFromEvent(img, e);
        if (!coords) {
            loupe.style.display = 'none';
            return;
        }

        loupe.style.display = 'block';

        // Position loupe centered at cursor (with fallback offset if at edge)
        loupe.style.left = `${coords.clientX}px`;
        loupe.style.top = `${coords.clientY}px`;

        // Render zoomed image area
        ctx.clearRect(0, 0, size, size);
        ctx.imageSmoothingEnabled = false;
        if ('mozImageSmoothingEnabled' in ctx) ctx.mozImageSmoothingEnabled = false;
        if ('webkitImageSmoothingEnabled' in ctx) ctx.webkitImageSmoothingEnabled = false;
        if ('msImageSmoothingEnabled' in ctx) ctx.msImageSmoothingEnabled = false;

        const srcW = size / zoom;
        const srcH = size / zoom;
        const srcX = coords.x - srcW / 2;
        const srcY = coords.y - srcH / 2;

        try {
            ctx.drawImage(img, srcX, srcY, srcW, srcH, 0, 0, size, size);
        } catch (_) {
            // In case image is not yet fully loaded
        }

        // Draw crosshair and highlight target pixel
        const centerX = size / 2;
        const centerY = size / 2;
        const pixelBoxSize = zoom;

        // Helper to draw crosshair lines (DRY)
        const drawCrosshairLines = (strokeStyle, lineWidth) => {
            ctx.strokeStyle = strokeStyle;
            ctx.lineWidth = lineWidth;
            ctx.beginPath();
            ctx.moveTo(0, centerY);
            ctx.lineTo(size, centerY);
            ctx.moveTo(centerX, 0);
            ctx.lineTo(centerX, size);
            ctx.stroke();
        };

        // Draw outer crosshair shadows for contrast (3px) and inner lines (1px)
        drawCrosshairLines('rgba(0, 0, 0, 0.8)', 3);
        drawCrosshairLines('rgba(255, 255, 255, 0.9)', 1);

        // Draw central target pixel box
        ctx.strokeStyle = '#ff3838';
        ctx.lineWidth = 2;
        ctx.strokeRect(
            Math.floor(centerX - pixelBoxSize / 2),
            Math.floor(centerY - pixelBoxSize / 2),
            pixelBoxSize,
            pixelBoxSize
        );

        // Update coordinate badge
        badge.textContent = `X: ${coords.x} | Y: ${coords.y}`;
    }

    img.addEventListener('mouseenter', (e) => {
        updateLoupe(e);
    });

    img.addEventListener('mousemove', (e) => {
        updateLoupe(e);
    });

    img.addEventListener('mouseleave', () => {
        loupe.style.display = 'none';
    });

    img.addEventListener('click', (e) => {
        imageCoordinates(e);
    });
}

// Global exports for inline onclick attributes and Vite ES module compatibility
if (typeof window !== 'undefined') {
    window.addBannerOptionGroup = addBannerOptionGroup;
    window.imageCoordinates = imageCoordinates;
    window.getCoordinatesFromEvent = getCoordinatesFromEvent;
    window.initBannerMagnifier = initBannerMagnifier;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBannerMagnifier);
    } else {
        initBannerMagnifier();
    }
}
