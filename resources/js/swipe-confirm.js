// Tombol geser: aksi baru jalan setelah knop digeser sampai ujung, supaya tidak terpencet tanpa sengaja.
export default () => ({
    x: 0,
    max: 0,
    dragging: false,
    done: false,
    startX: 0,

    start(event) {
        if (this.done) return;
        this.max = this.$refs.track.clientWidth - this.$refs.knob.clientWidth - 8;
        this.dragging = true;
        this.startX = event.clientX - this.x;
        event.target.setPointerCapture?.(event.pointerId);
    },

    move(event) {
        if (!this.dragging) return;
        this.x = Math.min(Math.max(event.clientX - this.startX, 0), this.max);
    },

    end() {
        if (!this.dragging) return;
        this.dragging = false;
        if (this.x >= this.max * 0.85) {
            this.x = this.max;
            this.done = true;
            this.$el.closest('form').requestSubmit();
        } else {
            this.x = 0;
        }
    },

    // Keyboard: Enter/Spasi tetap bisa memicu aksi.
    confirm() {
        if (this.done) return;
        this.max = this.$refs.track.clientWidth - this.$refs.knob.clientWidth - 8;
        this.x = this.max;
        this.done = true;
        this.$el.closest('form').requestSubmit();
    },

    get progress() {
        return this.max ? this.x / this.max : 0;
    },
});
