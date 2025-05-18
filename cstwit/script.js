document.getElementById('createPostForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('api/posts/create.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadPosts(); // Refresh feed after posting
            this.reset();
        } else {
            alert(data.error || 'Failed to post');
        }
    });
});

function loadPosts(tab = 'for_you') {
    fetch(`api/posts/feed.php?tab=${tab}`, { credentials: 'include' })
        .then(res => res.json())
        .then(posts => {
            const feed = document.getElementById('postsFeed');
            feed.innerHTML = '';
            if (posts.length === 0) {
                feed.innerHTML = '<p>No posts yet.</p>';
            } else {
                posts.forEach(post => {
                    feed.innerHTML += displayPost(post);
                });
            }
        });
}

function displayPost(post) {
    return `
        <div class="post">
            <strong>${post.username}</strong>
            <p>${post.content}</p>
            ${post.image_path ? `<img src="${post.image_path}" alt="Post image">` : ''}
            <span>${post.created_at}</span>
        </div>
    `;
}

// Initial load
loadPosts();

//Tab Switching Logic (JavaScript)
document.querySelectorAll('.tab').forEach(tabBtn => {
    tabBtn.addEventListener('click', function() {
        const tab = this.dataset.tab;
        loadPosts(tab);
    });
});
