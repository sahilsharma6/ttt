<div class="my-3">
    <?php

    $tags = $current_post['tags'];
    $tags_array = explode(',', $tags);

    if (!empty($tags)) {
        echo '<h6 class="text-muted p-2">Tags</h6>';
    } else {
        echo '';
    }

    foreach ($tags_array as $tag) {
        echo '<span class="badge bg-success px-3 py-2 mx-1 my-1">' . htmlspecialchars($tag) . '</span>';
    }
    ?>
</div>