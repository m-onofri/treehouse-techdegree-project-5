<div class="entry-list">
    <?php  foreach ($posts as $post) { ?>
        <article>
            <h2>
                <a href="/detail/<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a>
            </h2>
            <time datetime="<?php echo $post['date']; ?>">
                <?php echo strftime("%B %e, %G at %H:%M", strtotime($post['date'])); ?>
            </time>
            <p class="excerpt"><?php echo substr($post['body'], 0, 200); ?>
            <a href="/detail/<?php echo $post['id']; ?>">[Read more...]</a></p>
            <!-- Display all the tags for te current entry -->
            <?php 
            if (!empty($post['tags'])) {
                echo "<div class='tags-list'>";
                foreach ($post['tags'] as $tag) {
                    echo "<a href='tags.php?id=" . $tag['id'] . "' class='button button-tag'>" . $tag['name'] . "</a>";
                }
                echo "</div>";
            } 
            ?>
        </article>
    <?php } ?>
</div>

