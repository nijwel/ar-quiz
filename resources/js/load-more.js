/**
 * Infinite scroll for paginated lists.
 *
 * Usage: x-data="loadMore(nextPageUrl, itemsShown, '#grid')"
 * The server answers a JSON request for nextPageUrl with { html, next, shown }.
 * The next page loads when the x-ref="sentinel" element scrolls into view, or on load().
 */
export default (nextUrl, shown, target) => ({
    next: nextUrl,
    shown,
    loading: false,
    error: false,
    observer: null,

    init() {
        if (!('IntersectionObserver' in window)) return;

        // Start loading a little before the student reaches the bottom
        this.observer = new IntersectionObserver(
            (entries) => entries[0].isIntersecting && this.load(),
            { rootMargin: '400px' },
        );
        this.observer.observe(this.$refs.sentinel);
    },

    async load() {
        if (!this.next || this.loading) return;

        this.loading = true;
        this.error = false;

        try {
            const response = await fetch(this.next, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) throw new Error(response.statusText);

            const data = await response.json();
            document.querySelector(target).insertAdjacentHTML('beforeend', data.html);
            this.next = data.next;
            this.shown = data.shown;
        } catch {
            this.error = true;
        } finally {
            this.loading = false;
            if (!this.next) this.observer?.disconnect();
        }
    },

    destroy() {
        this.observer?.disconnect();
    },
});
