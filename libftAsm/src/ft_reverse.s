global _ft_reverse
extern _ft_strdupclean
extern _ft_strlen
_ft_reverse:
	push rdi
	call _ft_strlen
	mov rdi, rax
	push rax
	call _ft_strdupclean
	mov rsi, rax
	mov rdi, rsi
	pop rcx
	pop rsi
	push rsi
	cld
	rep movsb
	pop rax
	ret