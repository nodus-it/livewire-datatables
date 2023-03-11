<?php

use Nodus\Packages\LivewireDatatables\Services\Button;
use Nodus\Packages\LivewireDatatables\Tests\Data\Models\Post;
use Nodus\Packages\LivewireDatatables\Tests\Data\Models\User;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

it('can be initialized', function () {
    $user = User::factory()->make();
    $button = new Button('Details', 'users.show', ['id' => ':id']);
    assertEquals('Details', $button->getId());
    assertEquals('Details', $button->getLabel());
    assertEquals('http://localhost/user/' . $user->id, $button->getRoute($user));
    assertEquals('_self', $button->getTarget());
    assertEquals(null, $button->getIcon());
    assertEquals(1, $button->getRenderMode());
});

it('works with static route parameters', function () {
    $user = User::factory()->make();
    $button = new Button('Details', 'users.show', ['id' => '5']);
    assertEquals('http://localhost/user/5', $button->getRoute($user));
});

it('works with dynamic route parameters', function () {
    $user = Post::factory()->create(['id' => 12345, 'user_id' => User::factory()->create(['id' => 98765])->id]);
    $button = new Button('Details', 'posts.show', ['id' => ':user.id']);
    assertEquals('http://localhost/post/98765', $button->getRoute($user));
});

it('can change the link target', function () {
    $button = new Button('Details', 'users.show', ['id' => '5']);
    $button->setTarget('_blank');
    assertEquals('_blank', $button->getTarget());
});

it('can render icons', function () {
    $button = new Button('Details', 'users.show', ['id' => '5']);
    $button->setIcon('fa-user');
    assertEquals('fa-user', $button->getIcon());
    assertEquals(2, $button->getRenderMode());

    $button->setIcon('fa-user', false);
    assertEquals('fa-user', $button->getIcon());
    assertEquals(3, $button->getRenderMode());
});

it('supports custom css classes', function () {
    $button = new Button('Details', 'users.show', ['id' => '5']);
    assertNull($button->getClasses());
    $button->setClasses(['a', 'b']);
    assertEquals('a b', $button->getClasses());
});

it('works with the confirmation modal', function () {
    $button = new Button('Details', 'users.show', ['id' => '5']);
    $button->setConfirmation('a', 'b', 'c', 'd', 'e');
    assertEquals(
        [
            'enable'  => true,
            'text'    => 'a',
            'title'   => 'b',
            'confirm' => 'c',
            'cancel'  => 'd',
            'context' => 'e',
        ],
        $button->getConfirmation()
    );
});

it('supports conditional rendering', function () {
    $user1 = new User();
    $user1->admin = true;
    $user2 = new User();
    $user2->admin = false;

    $button = new Button('Details', 'users.show', ['id' => '5']);
    $button->setCondition(function (User $user) {
        return $user->admin;
    });

    assertTrue($button->isAllowedToRender($user1));
    assertFalse($button->isAllowedToRender($user2));
});
