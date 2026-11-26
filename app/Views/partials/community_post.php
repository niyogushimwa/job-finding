<?php
/** Expected: $post; optional $liked (bool) and $full (bool). Included only from community views. */
use App\Core\Auth;
use App\Core\Upload;

$full = $full ?? false;
$liked = $liked ?? false;
$categoryLabels = [
    'experience' => 'Experience',
    'question' => 'Question',
    'advice' => 'Advice',
    'interview' => 'Interview',
    'resource' => 'Resource',
    'achievement' => 'Achievement',
];
?>
<article class="card feed-post">
  <div class="post-head">
    <?php if (!empty($post['author_avatar'])): ?>
      <img class="avatar" src="<?= e(Upload::url((string) $post['author_avatar'])) ?>" alt="">
    <?php else: ?>
      <span class="avatar"><?= e(mb_substr((string) ($post['author_name'] ?? 'M'), 0, 1)) ?></span>
    <?php endif; ?>
    <div>
      <strong><?= e($post['author_name'] ?? 'Community member') ?></strong>
      <time datetime="<?= e($post['created_at'] ?? '') ?>"><?= e(time_ago($post['created_at'] ?? null)) ?></time>
    </div>
    <span class="tag tag-blue" style="margin-left:auto"><?= e($categoryLabels[$post['category']] ?? ucfirst((string) $post['category'])) ?></span>
  </div>

  <h3 class="mt-0"><a href="<?= e(url('community/' . (int) $post['id'])) ?>"><?= e($post['title']) ?></a></h3>
  <div class="small" style="white-space:pre-wrap"><?= e($full ? (string) $post['body'] : str_limit((string) $post['body'], 260)) ?></div>

  <?php if (!empty($post['image'])): ?>
    <img class="mt-2" src="<?= e(Upload::url((string) $post['image'])) ?>" alt="Post image" style="max-width:100%;border-radius:12px">
  <?php endif; ?>

  <div class="post-actions">
    <a class="btn btn-ghost btn-sm" href="<?= e(url('community/' . (int) $post['id'])) ?>"><?= icon('chat', 15) ?> <?= (int) ($post['comment_total'] ?? $post['comments_count'] ?? 0) ?> comments</a>
    <?php if (Auth::check()): ?>
      <form method="post" action="<?= e(url('community/' . (int) $post['id'] . '/like')) ?>">
        <?= csrf_field() ?>
        <button class="btn btn-outline btn-sm" type="submit"><?= icon('heart', 15) ?> <?= $liked ? 'Liked' : 'Like' ?> · <?= (int) ($post['likes_count'] ?? 0) ?></button>
      </form>
    <?php else: ?>
      <a class="btn btn-ghost btn-sm" href="<?= e(url('login')) ?>"><?= icon('heart', 15) ?> <?= (int) ($post['likes_count'] ?? 0) ?> likes</a>
    <?php endif; ?>
  </div>
</article>
