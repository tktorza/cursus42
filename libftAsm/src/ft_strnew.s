section .text
global _ft_strnew
extern _malloc
extern _ft_bzero
extern _ft_strlen
_ft_strnew:
	push rdi
	call _malloc

	cmp rax, 0
	je false

	mov rdi, rax
	pop rsi
	inc rsi
	push rdi
	call _ft_bzero
	pop rax

return:
	ret

false:
	mov rax, 0
	jmp return
