<h1><?=$title ?> </h1>

<?php if (is_array($tags)) :?>
    <ul>
        <?php foreach ($tags as $tag) :?>
            <li class='tag-thumb'>
                <a href='<?=$this->url->create("forum/view-tag/{$tag->tag_id}") ?>'>
                    <i class="fa fa-tag"></i>
                    <?=$tag->tag_text ?>
                    [<?=$tag->count ?>]
                </a>
            </li>    
        <?php endforeach; ?>        
    </ul>
<?php endif;?>    
