// script.js

document.addEventListener('DOMContentLoaded', () => {
  const postForm = document.getElementById('post');
  const postsContainer = document.getElementById('posts');

  // 投稿一覧を取得して表示
  async function loadPosts() {
    try {
      const response = await fetch('get_posts.php');
      if (!response.ok) throw new Error('投稿の取得に失敗しました。');
      const posts = await response.json();
      renderPosts(posts);
    } catch (error) {
      console.error(error);
      postsContainer.innerHTML = '<p>投稿の読み込みに失敗しました。</p>';
    }
  }

  // 投稿フォーム送信時
  postForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const formData = new FormData(postForm);

    try {
      const response = await fetch('post.php', {
        method: 'POST',
        body: formData,
      });
      if (!response.ok) throw new Error('投稿に失敗しました。');

      postForm.reset();
      await loadPosts(); // 投稿後に再読み込み
    } catch (error) {
      console.error(error);
      alert('投稿できませんでした。');
    }
  });

  // いいね処理
  postsContainer.addEventListener('click', async (event) => {
    if (event.target.classList.contains('like-btn')) {
      const postId = event.target.dataset.id;

      try {
        const formData = new FormData();
        formData.append('id', postId);

        const response = await fetch('like.php', {
          method: 'POST',
          body: formData,
        });

        if (!response.ok) throw new Error('いいねに失敗しました。');

        await loadPosts(); // いいね後に再読み込み
      } catch (error) {
        console.error(error);
        alert('いいねできませんでした。');
      }
    }
  });

  // 投稿一覧の描画
  function renderPosts(posts) {
    postsContainer.innerHTML = '';

    posts.forEach(post => {
      const postElement = document.createElement('div');
      postElement.className = 'post';
      postElement.innerHTML = `
        <p>${escapeHTML(post.text)}</p>
        <button class="like-btn" data-id="${post.id}">♡いいね (${post.likes})</button>
        <hr>
      `;
      postsContainer.appendChild(postElement);
    });
  }

  // HTMLエスケープ（XSS対策）
  function escapeHTML(str) {
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // 初回ロード
  loadPosts();
});
