section .text
global _ft_strdup
extern _ft_strlen
extern _malloc
_ft_strdup:
	push rbp
	mov rbp, rsp
    
	push rdi
	cmp rdi, 0
	je return

	call _ft_strlen
	inc rax
	push rax
	mov rdi, rax
	call _malloc
	mov rdi, rax
	pop rcx
	pop rsi
	push rdi
    cld
    rep movsb

return:
    pop rax
	mov rsp, rbp
	pop rbp
    ret