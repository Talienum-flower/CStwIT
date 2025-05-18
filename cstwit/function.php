<?php
function displayPosts($posts) {
    if (empty($posts)) {
        return '<p>No posts yet.</p>';
    }
    $html = '';
    foreach ($posts as $post) {
        $html .= '<div class="post">';
        $html .= '<strong>' . htmlspecialchars($post['username']) . '</strong>';
        $html .= '<p>' . nl2br(htmlspecialchars($post['content'])) . '</p>';
        if (!empty($post['image_path'])) {
            $html .= '<img src="' . htmlspecialchars($post['image_path']) . '" alt="Post image">';
        }
        $html .= '<span>' . htmlspecialchars($post['created_at']) . '</span>';
        $html .= '</div>';
    }
    return $html;
}

// Add any other functions you need
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>