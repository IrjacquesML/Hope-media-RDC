{{-- Commentaires publics (enseignements et actualités) : formulaire + liste + réponses de Hope Media RDC.
     Variables : $comments (collection), $action (URL du formulaire), $placeholder. --}}
<section class="form-card" id="commentaires" aria-labelledby="commentsTitle">
    <h2 id="commentsTitle">Commentaires <span class="muted" style="font:600 1rem var(--f-body)">({{ $comments->count() }})</span></h2>

    @if (session('comment_success'))
        <div class="alert ok" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>{{ session('comment_success') }}</span></div>
    @endif

    <form action="{{ $action }}" method="POST" novalidate class="form-grid" style="background:var(--bg);border:1px solid var(--line);border-radius:12px;padding:16px">
        @csrf
        <h3 style="margin:0;font:400 1.3rem/1 var(--f-display);letter-spacing:.04em">Laisser un commentaire</h3>
        <div class="form-grid two">
            <div class="field">
                <label for="author_name">Nom <span class="req">*</span></label>
                <input class="input" type="text" id="author_name" name="author_name" value="{{ old('author_name') }}" required maxlength="100" placeholder="Votre nom" autocomplete="name" @error('author_name') aria-invalid="true" @enderror>
                @error('author_name')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="author_email">Email <span class="hint" style="display:inline">(optionnel, non affiché)</span></label>
                <input class="input" type="email" id="author_email" name="author_email" value="{{ old('author_email') }}" maxlength="255" placeholder="votre@email.com" autocomplete="email" @error('author_email') aria-invalid="true" @enderror>
                @error('author_email')<p class="error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="field">
            <label for="content">Commentaire <span class="req">*</span></label>
            <textarea class="textarea" id="content" name="content" rows="4" required maxlength="2000" placeholder="{{ $placeholder }}" @error('content') aria-invalid="true" @enderror>{{ old('content') }}</textarea>
            @error('content')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="toolbar">
            <p class="hint"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Aucun compte requis. Votre commentaire sera visible immédiatement.</p>
            <button class="btn btn-gold" type="submit"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i>Envoyer</button>
        </div>
    </form>

    @if ($comments->count() > 0)
        <div class="comments">
            @foreach ($comments as $comment)
                <div class="comment">
                    <span class="avatar" aria-hidden="true">{{ mb_substr($comment->author_name, 0, 1) }}</span>
                    <div>
                        <div class="comment-head">
                            <strong>{{ $comment->author_name }}</strong>
                            <span class="muted">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p>{{ $comment->content }}</p>
                    </div>
                    @if ($comment->replies->isNotEmpty())
                        <div class="replies">
                            @foreach ($comment->replies as $reply)
                                <div class="reply">
                                    <div class="comment-head">
                                        <span class="official"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Hope Media RDC</span>
                                        <strong>{{ $reply->author_name }}</strong>
                                        <span class="muted">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p>{{ $reply->content }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p class="muted" style="margin:0"><i class="fa-regular fa-comment" aria-hidden="true"></i> Aucun commentaire pour l'instant. Soyez le premier à réagir !</p>
    @endif
</section>
