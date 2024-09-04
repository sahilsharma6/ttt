<style>
    .share-container {
        position: relative;
        display: inline-block;
    }

    .share-btn {
        cursor: pointer;
        font-size: 1.2rem;
        transition: background-color 0.3s, transform 0.3s;
    }

    .share-btn:hover {
        transform: scale(1.1);
    }

    .share-options {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 40px;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
        min-width: 160px;
        text-align: left;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s, visibility 0.3s;
    }

    .share-options.show-options {
        display: block;
        opacity: 1;
        visibility: visible;
    }

    .share-option {
        color: #333;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        transition: background-color 0.3s, color 0.3s;
    }

    .share-option:hover {
        background-color: #f1f1f1;
        color: #007bff;
    }

    .copy {
        cursor: pointer;
    }
</style>

<div class="d-flex align-items-center justify-content-between pt-3">
    <div>
        <?php
        $date = new DateTime($current_post['created_at']);
        // echo $date->format('d M Y'); // Outputs: 24 Aug 2024
        ?>
    </div>
    <div class="share-container">
        <button class="share-btn">
            <span class="mx-3"><i class="fa-regular fa-share-from-square"></i> Share</span>
        </button>
        <!-- Share options -->
        <div class="share-options">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://yourwebsite.com/post.php?category_id=' . $category_id . '&subcategory_id=' . $subcategory_id . '&post_id=' . $post_id); ?>"
                target="_blank" class="share-option">
                <i class="fab fa-facebook-f"></i> Share on Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://yourwebsite.com/post.php?category_id=' . $category_id . '&subcategory_id=' . $subcategory_id . '&post_id=' . $post_id); ?>&text=<?php echo urlencode($current_post['title']); ?>"
                target="_blank" class="share-option">
                <i class="fab fa-twitter"></i> Share on Twitter
            </a>
            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode('https://yourwebsite.com/post.php?category_id=' . $category_id . '&subcategory_id=' . $subcategory_id . '&post_id=' . $post_id); ?>"
                target="_blank" class="share-option">
                <i class="fab fa-whatsapp"></i> Share on WhatsApp
            </a>
            <span class="share-option copy">
                <i class="fa fa-link"></i> Copy Link
                <input type="text" hidden
                    value="https://yourwebsite.com/post.php?category_id=<?php echo $category_id; ?>&subcategory_id=<?php echo $subcategory_id; ?>&post_id=<?php echo $post_id; ?>"
                    class="copyLinkInput" readonly>
            </span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Function to toggle share options
        function toggleShareOptions(event) {
            const shareBtn = event.currentTarget;
            const shareOptions = shareBtn.nextElementSibling; // share-options div

            // Hide all other share options
            document.querySelectorAll('.share-options').forEach(option => {
                if (option !== shareOptions) {
                    option.classList.remove('show-options');
                }
            });

            // Toggle the current share options
            shareOptions.classList.toggle('show-options');
        }

        // Function to copy link
        function copyLink(event) {
            const copyInput = event.currentTarget.querySelector('.copyLinkInput');
            const copyText = copyInput.value;

            navigator.clipboard.writeText(copyText).then(() => {
                showToast('success', 'Link copied to clipboard!');
            }).catch(err => {
                console.error('Could not copy text: ', err);
            });
        }

        // Attach event listeners
        document.querySelectorAll('.share-btn').forEach(btn => {
            btn.addEventListener('click', toggleShareOptions);
        });

        document.querySelectorAll('.copy').forEach(copyOption => {
            copyOption.addEventListener('click', copyLink);
        });
    });

    // Optional: Show toast message function (you can implement your own)

</script>