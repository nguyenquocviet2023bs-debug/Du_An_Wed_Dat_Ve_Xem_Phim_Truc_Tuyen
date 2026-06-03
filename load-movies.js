async function loadMoviesFromDatabase() {
    try {
        const formData = new FormData();
        formData.append('action', 'getMovies');
        
        const response = await fetch('logicDB.php', { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success && data.movies) {
            renderMoviesGrid(data.movies);
            window.moviesData = data.movies.map(m => ({
                id: m.id,
                name: m.ten_phim,
                genre: m.the_loai || 'Chưa rõ',
                rating: 8.5,
                image: m.hinh_anh_url,
                duration: m.thoi_luong
            }));
        }
    } catch (error) {
        console.error('Lỗi khi tải phim:', error);
    }
}

function renderMoviesGrid(movies) {
    const grid = document.querySelector('.movies-grid');
    if (!grid) return;
    
    if (!movies || movies.length === 0) {
        grid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: #999;">Chưa có phim nào.</p>';
        return;
    }
    
    grid.innerHTML = movies.map(movie => `
        <div class="movie-card">
            <div class="movie-poster">
                ${movie.hinh_anh_url 
                    ? `<img src="${escapeHtml(movie.hinh_anh_url)}" alt="${escapeHtml(movie.ten_phim)}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                       <div class="movie-no-image" style="display:none;">
                           <i class="fas fa-film" style="font-size: 60px; color: #ddd;"></i>
                       </div>`
                    : `<div class="movie-no-image">
                           <i class="fas fa-film" style="font-size: 60px; color: #ddd;"></i>
                       </div>`
                }
                <div class="movie-overlay">
                    <button class="btn-trailer">
                        <i class="fas fa-play"></i> Trailer
                    </button>
                </div>
                <span class="movie-rating">8.5</span>
                <span class="movie-age">T13</span>
            </div>
            <h3 class="movie-title">${escapeHtml(movie.ten_phim)}</h3>
            <p class="movie-info">${escapeHtml(movie.the_loai || 'Phim')} | ${movie.thoi_luong || 120} phút</p>
            <button class="btn-book-movie">Mua vé</button>
        </div>
    `).join('');
    
    initBookingButtons();
    initMovieCards();
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadMoviesFromDatabase);
} else {
    loadMoviesFromDatabase();
}
